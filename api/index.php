<?php

/**
 * vTiger API router — books routes split into Controller/Model + Auth helper class.
 * Log: vtigercrm/api/api_debug.log
 */

use Dotenv\Dotenv;

date_default_timezone_set('UTC');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin', '*');
header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/* ---------------- Config ---------------- */
const API_KEY  = '02i34nm34j32423j4lk983u';
const LOG_FILE = __DIR__ . '/api_debug.log';

require_once __DIR__ . '/helpers/Utils.php';
require_once __DIR__ . '/helpers/Validate.php';
/* --------------- Utils ------------------ */

use function Api\Helper\{
    logf,
    json_ok,
    json_err,
    get_path_after_api
};


/* --------- Bootstrap vtiger (buffer only here) ---------- */

try {
    $ROOT = realpath(__DIR__ . '/..'); // vtigercrm/
    if (!$ROOT) throw new Exception('Root resolve failed');
    chdir($ROOT);
    set_include_path($ROOT . PATH_SEPARATOR . get_include_path());

    ob_start(); // capture stray output from includes

    if (file_exists($ROOT . '/vendor/autoload.php')) {
        require_once $ROOT . '/vendor/autoload.php';
    } else {
        throw new Exception('vendor/autoload.php not found — run `composer install` in vtiger root.');
    }

    require_once 'config.inc.php';
    require_once 'include/utils/utils.php';
    require_once 'include/database/PearDatabase.php';
    require_once 'modules/Users/Users.php';
    require_once 'include/QueryGenerator/QueryGenerator.php';

    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();

    define('OAUTH_ISSUER',      $_ENV['OAUTH_ISSUER']      ?? getenv('OAUTH_ISSUER')      ?: 'http://localhost/vtiger-gpm/');
    define('OAUTH_AUDIENCE',    $_ENV['OAUTH_AUDIENCE']    ?? getenv('OAUTH_AUDIENCE')    ?: 'vtiger-api');
    define('OAUTH_SIGNING_KEY', $_ENV['OAUTH_SIGNING_KEY'] ?? getenv('OAUTH_SIGNING_KEY') ?? 'CHANGE_ME');
    define('OAUTH_ACCESS_TTL',  (int)($_ENV['OAUTH_ACCESS_TTL']  ?? getenv('OAUTH_ACCESS_TTL')  ?: 3600));
    define('OAUTH_REFRESH_TTL', (int)($_ENV['OAUTH_REFRESH_TTL'] ?? getenv('OAUTH_REFRESH_TTL') ?: 2592000));

    $db = PearDatabase::getInstance();
    if (!$db) {
        throw new Exception('Database unavailable');
    }

    $stray = ob_get_clean();
    if ($stray !== '' && trim($stray) !== '') logf('bootstrap.stray_output', ['bytes' => strlen($stray), 'content' => trim($stray)]);
} catch (Throwable $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    logf('bootstrap.exception', ['err' => $e->getMessage(), 'include_path' => get_include_path(), 'cwd' => getcwd()]);
    json_err('Bootstrap error', 500);
}


/* ---------------- Include app classes ---------------- */
require_once __DIR__ . '/helpers/Auth.php';
require_once __DIR__ . '/helpers/OAuth.php';
require_once __DIR__ . '/controllers/AssetsController.php';
require_once __DIR__ . '/models/AssetsModel.php';
require_once __DIR__ . '/controllers/OrdersController.php';
require_once __DIR__ . '/models/OrdersModel.php';
require_once __DIR__ . '/controllers/KryptoTradesController.php';
require_once __DIR__ . '/models/KryptoTradesModel.php';

/* ---------------- Routing ---------------- */
try {
    // Auth (class-based in helpers/)
    // \Api\Helper\Auth::requireApiKey(API_KEY);

    $method = $_SERVER['REQUEST_METHOD'];
    $path   = get_path_after_api();
    $segments = $path === '' ? [] : explode('/', $path);
    $resource = $segments[0] ?? '';
    $id = $segments[1] ?? null;

    // Public endpoints
    if ($resource === 'oauth' && ($segments[1] ?? '') === 'token' && $method === 'POST') {
        // Add logging context
        logf('oauth.token', ['method' => $method, 'path' => $path, 'qs' => $_GET]);
        \Api\Helper\OAuth::tokenEndpoint($db);
        exit;
    }

    switch ($path) {
        case '':
        case '/':
            $hash =  password_hash('supersecret', PASSWORD_DEFAULT);

            json_ok(['status' => 'ok', 'service' => 'vtiger-api', 'message' => 'Welcome to the vtiger API', 'php' => PHP_VERSION, 'hash_example' => $hash]);

        case 'metals':
            \Api\Helper\OAuth::requireBearer(['assets:read']); // example scope
            json_ok(['status' => 'ok', 'service' => 'vtiger-api', 'message' => 'Get metals', 'php' => PHP_VERSION]);
            break;

        case 'assets':
            \Api\Helper\OAuth::requireBearer(['assets:read']); // example scope
            $model      = new \Api\Model\AssetsModel($db);
            $controller = new \Api\Controller\AssetsController($model);

            if ($path === 'assets') {
                if ($method === 'GET') {
                    $res = $controller->list($_GET);
                    json_ok($res);
                }
                if ($method === 'POST') {
                    $res = $controller->create(file_get_contents('php://input'));
                    json_ok($res, 201);
                }
                json_err('Method not allowed', 405);
            }
            break;

        case 'trades':
            // \Api\Helper\OAuth::requireBearer(['assets:read']); // example scope
            $model      = new \Api\Model\KryptoTradesModel($db);
            $controller = new \Api\Controller\KryptoTradesController($model);

            if ($path === 'trades') {
                if ($method === 'GET') {
                    $res = $controller->list($_GET);
                    json_ok($res);
                }
                if ($method === 'POST') {
                    $res = $controller->create(file_get_contents('php://input'));
                    json_ok($res, 201);
                }
                json_err('Method not allowed', 405);
            }
            break;

        case 'orders':
            \Api\Helper\OAuth::requireBearer(['assets:read']); // example scope
            $model      = new \Api\Model\OrdersModel($db);
            $controller = new \Api\Controller\OrdersController($model);

            if ($path === 'orders') {
                if ($method === 'GET') {
                    $res = $controller->list($_GET);
                    json_ok($res);
                }
                if ($method === 'POST') {
                    $res = $controller->create(file_get_contents('php://input'));
                    json_ok($res, 201);
                }
                json_err('Method not allowed', 405);
            }
            break;

        default:
            json_err('Endpoint not found please check your request', 404);
    }
} catch (Throwable $e) {
    logf('handler.exception', ['err' => $e->getMessage()]);
    json_err('Server error', 500);
}
