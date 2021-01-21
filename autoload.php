<?php

spl_autoload_register(function (String $class) {
    $sourcePath = APP . DIRECTORY_SEPARATOR;
    $repo = 'App\Repository\\';
    if (substr($class, 0, strlen($repo)) == $repo) {
        $class = $repo . explode('\\', substr($class, strlen($repo)))[0];
    }
    $replaceDirectorySeparator = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $filePath = $sourcePath . $replaceDirectorySeparator . '.php';
    if (file_exists($filePath)) {
        require($filePath);
    }
});