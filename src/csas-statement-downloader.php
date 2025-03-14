<?php

declare(strict_types=1);

/**
 * This file is part of the CSas Statement Tools package
 *
 * https://github.com/VitexSoftware/csas-statement-tools
 *
 * (c) Vítězslav Dvořák <info@vitexsoftware.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet\CSas;

use Ease\Shared;
use VitexSoftware\CSas\ApiClient;
use VitexSoftware\CSas\Statementor;

require_once '../vendor/autoload.php';

\define('APP_NAME', 'CSas Statement Downloader');

if (\array_key_exists(1, $argv) && $argv[1] === '-h') {
    echo 'csas-statement-downloader [save/to/directory] [format] [path/to/.env]';
    echo "\n";

    exit;
}

Shared::init(['CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER'], \array_key_exists(3, $argv) ? $argv[3] : '../.env');
$engine = new Statementor(Shared::cfg('ACCOUNT_NUMBER'));

if (\Ease\Shared::cfg('APP_DEBUG', false)) {
    $engine->logBanner();
}

if (ApiClient::checkCertificatePresence(Shared::cfg('CERT_FILE'), true) === false) {
    $engine->addStatusMessage(sprintf(_('Certificate file %s problem'), Shared::cfg('CERT_FILE')), 'error');

    exit(1);
}

$engine->setScope(Shared::cfg('STATEMENT_IMPORT_SCOPE', 'last_month'));

try {
    $status = 'ok';
    $exitcode = 0;
    $statements = $engine->getStatements(Shared::cfg('ACCOUNT_CURRENCY', 'CZK'), Shared::cfg('STATEMENT_LINE', 'MAIN'));
} catch (\VitexSoftware\CSas\ApiException $exc) {
    $status = $exc->getCode().': error';
    $exitcode = (int) $exc->getCode();
}

if (empty($statements) === false) {
    $engine->download(
        \array_key_exists(1, $argv) ? $argv[1] : Shared::cfg('STATEMENTS_DIR', getcwd()),
        $statements,
        \array_key_exists(2, $argv) ? $argv[2] : Shared::cfg('STATEMENT_FORMAT', 'pdf'),
    );
} else {
    echo "no statements returned\n";
}

exit($exitcode);
