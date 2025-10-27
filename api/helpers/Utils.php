<?php

namespace Api\Helper;

const LOG_FILE = __DIR__ . '/../api_debug.log';

/**
 * Log helper (simple JSON line logs)
 */
function logf(string $msg, array $ctx = []): void
{
    @file_put_contents(
        LOG_FILE,
        sprintf("[%s] %s %s%s\n", date('c'), $msg, $ctx ? '| ' : '', $ctx ? json_encode($ctx, JSON_UNESCAPED_SLASHES) : ''),
        FILE_APPEND
    );
}

/**
 * Return a standardized JSON success response
 */
function json_ok($data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode([
        'success'   => true,
        'data'      => $data,
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
    exit;
}

/**
 * Return a standardized JSON error response
 */
function json_err(string $msg, int $code = 400): void
{
    http_response_code($code);
    echo json_encode([
        'success'   => false,
        'error'     => $msg,
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
    exit;
}

/**
 * Extract the relative path after /api/
 */
function get_path_after_api(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $pos  = stripos($path, '/api/');
    if ($pos !== false) {
        $path = substr($path, $pos + 5);
    }
    return trim($path, '/');
}
