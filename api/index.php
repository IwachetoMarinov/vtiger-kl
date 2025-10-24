<?php

/**
 * vTiger API router — books routes split into Controller/Model + Auth helper class.
 * Log: vtigercrm/api/api_debug.log
 */

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

/* --------------- Utils ------------------ */
function logf(string $msg, array $ctx = []): void
{
    @file_put_contents(
        LOG_FILE,
        sprintf("[%s] %s %s%s\n", date('c'), $msg, $ctx ? '| ' : '', $ctx ? json_encode($ctx, JSON_UNESCAPED_SLASHES) : ''),
        FILE_APPEND
    );
}

function json_ok($data, int $code = 200)
{
    http_response_code($code);
    echo json_encode(['success' => true, 'data' => $data, 'timestamp' => date('c')], JSON_PRETTY_PRINT);
    exit;
}

function json_err(string $msg, int $code = 400)
{
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg, 'timestamp' => date('c')], JSON_PRETTY_PRINT);
    exit;
}

function get_path_after_api(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $pos  = stripos($path, '/api/');
    if ($pos !== false) $path = substr($path, $pos + 5);
    return trim($path, '/');
}

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
    // require_once 'include/loader.php';
    require_once 'include/utils/utils.php';
    require_once 'include/database/PearDatabase.php';
    require_once 'modules/Users/Users.php';
    require_once 'include/QueryGenerator/QueryGenerator.php';

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
require_once __DIR__ . '/controllers/AssetsController.php';
require_once __DIR__ . '/models/AssetsModel.php';

/* ---------------- Routing ---------------- */
try {
    // Auth (class-based in helpers/)
    \Api\Helper\Auth::requireApiKey(API_KEY);

    $method = $_SERVER['REQUEST_METHOD'];
    $path   = get_path_after_api();
    logf('request', ['method' => $method, 'path' => $path, 'qs' => $_GET]);

    switch ($path) {
        case '':
        case '/':
            json_ok(['status' => 'ok', 'service' => 'vtiger-api', 'message' => 'Welcome to the vtiger API', 'php' => PHP_VERSION]);

        case 'metals':
            json_ok(['status' => 'ok', 'service' => 'vtiger-api', 'message' => 'Get metals', 'php' => PHP_VERSION]);
            break;

        case 'assets':
            $model      = new \Api\Model\AssetsModel($db);
            $controller = new \Api\Controller\AssetsController($model);

            if ($path === 'assets') {
                if ($method === 'GET') {
                    $res = $controller->list($_GET);
                    json_ok($res);
                    // json_ok(['status' => 'ok', 'service' => 'vtiger-api', 'message' => 'Get assets', 'php' => PHP_VERSION]);
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
