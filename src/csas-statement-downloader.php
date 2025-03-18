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

require_once '../vendor/autoload.php';

\define('APP_NAME', 'CSas Statement Downloader');

if (\array_key_exists(1, $argv) && $argv[1] === '-h') {
    echo 'csas-statement-downloader [save/to/directory] [format] [path/to/.env]';
    echo "\n";

    exit;
}

$options = getopt('o::e::', ['output::environment::']);
\Ease\Shared::init(
    ['CSAS_API_KEY', 'CSAS_ACCESS_TOKEN', 'CSAS_ACCOUNT_UUID', 'CSAS_ACCOUNT_IBAN'],
    \array_key_exists('environment', $options) ? $options['environment'] : (\array_key_exists('e', $options) ? $options['e'] : '../.env'),
);
$destination = \array_key_exists('output', $options) ? $options['output'] : (array_key_exists('o', $options) ? $options['o'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout'));

$engine = new Statementor(Shared::cfg('CSAS_ACCOUNT_UUID'), Shared::cfg('CSAS_ACCOUNT_IBAN'), Shared::cfg('IMPORT_SCOPE', 'last_month'));

if (\Ease\Shared::cfg('APP_DEBUG', false)) {
    $engine->logBanner($engine->getAccountNumber().' '.$engine->getScopeSymbolic());
}

try {
    $status = 'ok';
    $exitcode = 0;
    $statements = $engine->getStatements(Shared::cfg('STATEMENT_FORMAT', 'pdf'));
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
