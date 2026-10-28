<?php

/**
 * Veridian — root entry point.
 *
 * Allows the WHOLE application to be uploaded into a web-accessible folder
 * (public_html on cPanel, public_html on CyberPanel) and still work.
 *
 * The companion .htaccess in this same folder:
 *   - serves static assets (css/js/images/...) out of the public/ subfolder
 *   - sends every other request to this file
 *
 * The original Laravel entry point (public/index.php) is kept untouched, so
 * the "docroot = public/" hosting style continues to work exactly as before.
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode.
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader.
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request.
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
