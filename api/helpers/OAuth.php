<?php

namespace Api\Helper;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class OAuth
{
    /** Issue access(+refresh) tokens for client_credentials */
    public static function tokenEndpoint(\PearDatabase $db)
    {
        $input = self::jsonBody();

        $grant = strtolower($input['grant_type'] ?? ($_POST['grant_type'] ?? ''));
        if ($grant !== 'client_credentials' && $grant !== 'refresh_token') {
            self::jsonErr('unsupported_grant_type', 400);
        }

        if ($grant === 'client_credentials') {
            $clientId = $input['client_id'] ?? $_POST['client_id'] ?? null;
            $secret   = $input['client_secret'] ?? $_POST['client_secret'] ?? null;
            $scopeReq = trim($input['scope'] ?? '');


            if (!$clientId || !$secret) self::jsonErr('invalid_client 1', 401);

            $client = self::loadClient($db, $clientId);

            if (!$client || !$client['is_active']) self::jsonErr('invalid_client 2', 401);
            if (!password_verify($secret, $client['client_secret_hash'])) self::jsonErr('invalid_client 3', 401);

            $scopesAllowed = self::normalizeScopes($client['scopes']);
            $scopes = self::selectScopes($scopeReq, $scopesAllowed);

            $access  = self::issueAccessToken($clientId, $scopes);
            $refresh = self::issueRefreshToken($db, $clientId);

            self::jsonOk([
                'token_type'    => 'Bearer',
                'access_token'  => $access['jwt'],
                'expires_in'    => $access['ttl'],
                'scope'         => implode(' ', $scopes),
                'refresh_token' => $refresh,
            ]);
        }

        if ($grant === 'refresh_token') {
            $refresh = $input['refresh_token'] ?? $_POST['refresh_token'] ?? null;
            if (!$refresh) self::jsonErr('invalid_request', 400);
            $data = self::useRefreshToken($db, $refresh);
            $access = self::issueAccessToken($data['client_id'], $data['scopes'], $data['sub'] ?? null);
            self::jsonOk([
                'token_type'    => 'Bearer',
                'access_token'  => $access['jwt'],
                'expires_in'    => $access['ttl'],
                'scope'         => implode(' ', $data['scopes'])
            ]);
        }
    }

    /** Protect routes: require a valid Bearer token and (optional) scopes */
    public static function requireBearer(array $requiredScopes = []): array
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            self::unauthorized('missing_bearer');
        }
        $jwt = $m[1];

        try {
            $tok = JWT::decode($jwt, new Key(OAUTH_SIGNING_KEY, 'HS256'));
        } catch (\Throwable $e) {
            // Add logging context if needed
            if (function_exists('logf'))
                logf('oauth.token_decode_fail', ['error' => $e->getMessage()]);
            self::unauthorized('invalid_token');
        }

        // exp, nbf, iss, aud checks
        $now = time();
        if (!empty($tok->nbf) && $now < (int)$tok->nbf) self::unauthorized('token_not_active');
        if (!empty($tok->exp) && $now >= (int)$tok->exp) self::unauthorized('token_expired');
        if (!empty($tok->iss) && $tok->iss !== OAUTH_ISSUER) self::unauthorized('bad_issuer');
        if (!empty($tok->aud) && $tok->aud !== OAUTH_AUDIENCE) self::unauthorized('bad_audience');

        // scope check
        $scopes = isset($tok->scope) ? explode(' ', (string)$tok->scope) : [];
        foreach ($requiredScopes as $s) {
            if (!in_array($s, $scopes, true)) self::forbidden('insufficient_scope');
        }

        // Return claims for downstream use
        return [
            'client_id' => $tok->cid ?? null,
            'sub'       => $tok->sub ?? null,
            'scopes'    => $scopes,
            'claims'    => (array)$tok,
        ];
    }

    /* ---------------- internals ---------------- */

    private static function issueAccessToken(string $clientId, array $scopes, ?string $sub = null): array
    {
        $now = time();
        $ttl = OAUTH_ACCESS_TTL;
        $payload = [
            'iss'   => OAUTH_ISSUER,
            'aud'   => OAUTH_AUDIENCE,
            'iat'   => $now,
            'nbf'   => $now,
            'exp'   => $now + $ttl,
            'cid'   => $clientId,
            'scope' => implode(' ', $scopes),
        ];
        if ($sub) $payload['sub'] = $sub;

        $jwt = JWT::encode($payload, OAUTH_SIGNING_KEY, 'HS256');
        return ['jwt' => $jwt, 'ttl' => $ttl];
    }

    private static function issueRefreshToken(\PearDatabase $db, string $clientId): string
    {
        $raw = bin2hex(random_bytes(64)); // 128 hex chars
        $hash = password_hash($raw, PASSWORD_DEFAULT);
        $exp  = date('Y-m-d H:i:s', time() + OAUTH_REFRESH_TTL);
        $sql  = "INSERT INTO vtiger_api_refresh_tokens (client_id, token_hash, expires_at) VALUES (?, ?, ?)";
        $db->pquery($sql, [$clientId, $hash, $exp]);
        return $raw;
    }

    private static function useRefreshToken(\PearDatabase $db, string $raw)
    {
        // We can’t search by hash directly since it's bcrypt; fetch latest valid tokens for performance you might store sha256(raw) too.
        $sql = "SELECT id, client_id, token_hash, expires_at, revoked_at FROM vtiger_api_refresh_tokens WHERE revoked_at IS NULL ORDER BY id DESC LIMIT 1000";
        $res = $db->pquery($sql, []);
        while ($row = $res->FetchRow()) {
            if (password_verify($raw, $row['token_hash'])) {
                if (strtotime($row['expires_at']) <= time()) self::jsonErr('invalid_grant', 400);
                // rotate (revoke used one)
                $db->pquery("UPDATE vtiger_api_refresh_tokens SET revoked_at = NOW() WHERE id = ?", [$row['id']]);
                // scopes are tied to client; load them
                $client = self::loadClient($db, $row['client_id']);
                $scopes = self::normalizeScopes($client['scopes'] ?? '');
                return ['client_id' => $row['client_id'], 'scopes' => $scopes];
            }
        }
        self::jsonErr('invalid_grant', 400);
    }

    private static function loadClient(\PearDatabase $db, string $clientId): ?array
    {
        $sql = "SELECT client_id, client_secret_hash, scopes, is_active 
            FROM vtiger_api_clients WHERE client_id = ?";
        $res = $db->pquery($sql, [$clientId]);

        if (!$res) return null;

        $count = $db->num_rows($res);

        if ($count === 0) return null;

        $row = $db->query_result_rowdata($res, 0);
        $row = array_change_key_case($row, CASE_LOWER);
        $row['is_active'] = (int)$row['is_active'] === 1;

        return $row;
    }


    private static function normalizeScopes(string $s): array
    {
        $parts = preg_split('/\s+/', trim($s)) ?: [];
        return array_values(array_filter(array_unique($parts)));
    }

    private static function selectScopes(string $requested, array $allowed): array
    {
        if ($requested === '') return $allowed;
        $req = self::normalizeScopes($requested);
        return array_values(array_intersect($req, $allowed));
    }

    private static function jsonBody(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $js  = json_decode($raw, true);
        return is_array($js) ? $js : [];
    }

    private static function jsonOk(array $data, int $code = 200): void
    {
        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    private static function jsonErr(string $err, int $code): void
    {
        http_response_code($code);
        echo json_encode(['error' => $err], JSON_PRETTY_PRINT);
        exit;
    }

    private static function unauthorized(string $err): void
    {
        http_response_code(401);
        header('WWW-Authenticate: Bearer error="' . $err . '"');
        echo json_encode(['error' => $err], JSON_PRETTY_PRINT);
        exit;
    }

    private static function forbidden(string $err): void
    {
        http_response_code(403);
        echo json_encode(['error' => $err], JSON_PRETTY_PRINT);
        exit;
    }
}
