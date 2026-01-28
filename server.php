<?php

$publicPath = __DIR__.'/public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Emulate Apache "mod_rewrite" for the PHP built-in server.
// Only skip routing for actual files (not directories), otherwise folders like
// "public/soal" would shadow the "/soal" route and cause a built-in server 404.
if ($uri !== '/' && is_file($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
