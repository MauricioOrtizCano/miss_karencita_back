<?php

$apiRouteFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(base_path('routes/api'))
);

foreach ($apiRouteFiles as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        require $file->getPathname();
    }
}
