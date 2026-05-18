<?php
declare(strict_types=1);

function storage_bootstrap(): void
{
    $dirs = [
        UPLOAD_ROOT . '/resources',
        UPLOAD_ROOT . '/gallery/images',
        UPLOAD_ROOT . '/gallery/videos',
        UPLOAD_ROOT . '/announcements',
        UPLOAD_ROOT . '/temp',
    ];

    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            continue;
        }
        if (!mkdir($dir, 0777, true)) {
            error_log('[SCOTSA Storage] Could not create directory: ' . $dir);
            continue;
        }
        $index = $dir . '/index.html';
        if (!file_exists($index)) {
            file_put_contents($index, '');
        }
    }
}
