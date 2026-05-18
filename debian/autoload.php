<?php

declare(strict_types=1);

require_once '/usr/share/php/Ease/autoload.php';

spl_autoload_register(function (string $class): void {
    $prefixMap = [
        'SpojeNet\\CSas\\Csas\\' => '/usr/lib/csas-statement-tools/Csas/',
        'SpojeNet\\CSas\\'       => '/usr/share/php/CSasAccounts/',
    ];
    foreach ($prefixMap as $prefix => $base) {
        if (str_starts_with($class, $prefix)) {
            $file = $base . str_replace('\\', '/', substr($class, \strlen($prefix))) . '.php';
            if (file_exists($file)) {
                require $file;
            }
            return;
        }
    }
});
