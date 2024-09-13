<?php

declare(strict_types=1);

/**
 * This file is part of the RaiffeisenBank Statement Tools package
 *
 * https://github.com/Spoje-NET/pohoda-raiffeisenbank
 *
 * (c) Spoje.Net IT s.r.o. <https://spojenet.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet\RaiffeisenBank;

use Ease\Shared;
use VitexSoftware\Raiffeisenbank\ApiClient;
use VitexSoftware\Raiffeisenbank\Statementor;

require_once '../vendor/autoload.php';

\define('APP_NAME', 'RaiffeisenBank Statement Downloader');

if (\array_key_exists(1, $argv) && $argv[1] === '-h') {
    echo 'raiffeisenbank-statement-downloader [save/to/directory] [format] [path/to/.env]';
    echo "\n";

    exit;
}

Shared::init(['CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER'], \array_key_exists(3, $argv) ? $argv[3] : '../.env');
ApiClient::checkCertificatePresence(Shared::cfg('CERT_FILE'), true);
$engine = new Statementor(Shared::cfg('ACCOUNT_NUMBER'));

if (\Ease\Shared::cfg('APP_DEBUG', false)) {
    $engine->logBanner();
}

$engine->setScope(Shared::cfg('STATEMENT_IMPORT_SCOPE', 'last_month'));
$statements = $engine->getStatements(Shared::cfg('ACCOUNT_CURRENCY', 'CZK'), Shared::cfg('STATEMENT_LINE', 'MAIN'));

if (empty($statements) === false) {
    $engine->download(
        \array_key_exists(1, $argv) ? $argv[1] : Shared::cfg('STATEMENTS_DIR', getcwd()),
        $statements,
        \array_key_exists(2, $argv) ? $argv[2] : Shared::cfg('STATEMENT_FORMAT', 'pdf'),
    );
} else {
    echo "no statements returned\n";
}
