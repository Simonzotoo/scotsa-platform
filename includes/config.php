<?php
declare(strict_types=1);

define('APP_NAME', 'SCOTSA Platform');
define('BASE_URL', '/scotsa-platform');
define('UPLOAD_ROOT', dirname(__DIR__) . '/uploads');
define('MAX_UPLOAD_BYTES', 10 * 1024 * 1024);

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'scotsa_platform');
define('DB_USER', 'root');
define('DB_PASS', '');

require_once __DIR__ . '/storage.php';
storage_bootstrap();

require_once __DIR__ . '/images.php';

