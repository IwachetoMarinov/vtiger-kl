<?php

namespace Api\Helper;

class Auth
{
    public static function requireApiKey(string $expectedKey): void
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $key = $headers['X-API-Key'] ?? ($_GET['api_key'] ?? null);

        // optional lightweight logging without tight coupling
        if (function_exists('logf')) {
            logf('auth.check', ['provided' => $key ? 'yes' : 'no']);
        }

        if ($key !== $expectedKey) {
            if (function_exists('logf')) logf('auth.fail', ['remote' => $_SERVER['REMOTE_ADDR'] ?? '']);
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized', 'timestamp' => date('c')], JSON_PRETTY_PRINT);
            exit;
        }

        if (function_exists('logf')) logf('auth.ok', ['remote' => $_SERVER['REMOTE_ADDR'] ?? '']);
    }
}
