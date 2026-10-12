<?php

declare(strict_types=1);

require_once '/usr/share/php/Ease/autoload.php';
require_once '/usr/share/php/CSasAccounts/autoload.php';

// Own classes of csas-statement-tools (SpojeNet\CSas\Csas\*)
spl_autoload_register(function (string $class): void {
    $prefix = 'SpojeNet\\CSas\\Csas\\';
    if (strncmp($prefix, $class, \strlen($prefix)) !== 0) {
        return;
    }
    $file = '/usr/lib/csas-statement-tools/Csas/' . str_replace('\\', '/', substr($class, \strlen($prefix))) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
