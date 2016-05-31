<?php

require FRAMEWORK_ROOT_DIR.FRAMEWORK_LIBRARIES_DIRECTORY.'/minify/min/config.php';

require "$min_libPath/Minify/Loader.php";
Minify_Loader::register();

// set cache path and doc root if configured
$minifyCachePath = isset($min_cachePath)
    ? $min_cachePath
    : '';
if ($min_documentRoot) {
    $_SERVER['DOCUMENT_ROOT'] = $min_documentRoot;
}





