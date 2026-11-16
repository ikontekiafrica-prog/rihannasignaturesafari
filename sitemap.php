<?php
declare(strict_types=1);

require_once __DIR__ . '/portal/includes/config.php';
require_once __DIR__ . '/portal/includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');

try {
    $pdo = getDbConnection();
    echo buildSitemapXml($pdo);
} catch (Throwable $e) {
    $fallback = __DIR__ . '/sitemap.xml';
    if (is_file($fallback)) {
        readfile($fallback);
    } else {
        http_response_code(500);
        echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
    }
}
