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

use Ease\Shared as Shr;

require_once '../vendor/autoload.php';

\define('APP_NAME', 'CSasBalance');

/**
 * Get today's transactions list.
 */
$written = 0;
$exitcode = 0;
$options = getopt('o::e::', ['output::environment::']);
Shr::init(
    ['CSAS_API_KEY', 'CSAS_ACCESS_TOKEN', 'CSAS_ACCOUNT_IBAN'],
    \array_key_exists('environment', $options) ? $options['environment'] : (\array_key_exists('e', $options) ? $options['e'] : '../.env'),
);
$destination = \array_key_exists('output', $options) ? $options['output'] : (array_key_exists('o', $options) ? $options['o'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout'));

// Keep your tokens fresh using https://github.com/Spoje-NET/csas-authorize.git

$engine = new \Ease\Sand();
$engine->setObjectName(Shr::cfg('CSAS_ACCOUNT_IBAN'));

if (Shr::cfg('APP_DEBUG', false)) {
    $engine->logBanner();
}

$apiInstance = new \SpojeNET\CSas\Accounts\DefaultApi(new \SpojeNET\CSas\ApiClient(
    [
        'apikey' => Shr::cfg('CSAS_API_KEY'),
        'token' => Shr::cfg('CSAS_ACCESS_TOKEN'),
        'debug' => Shr::cfg('CSAS_API_DEBUG', false),
        'sandbox' => Shr::cfg('CSAS_SANDBOX_MODE'),
    ],
));

try {
    $balance = $apiInstance->getAccountBalance(Shr::cfg('CSAS_ACCOUNT_IBAN'));
    $written = file_put_contents($destination, json_encode($balance, Shr::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
} catch (\VitexSoftware\CSas\ApiException $exc) {
    $report['mesage'] = $exc->getMessage();

    $exitcode = $exc->getCode();

    if (!$exitcode) {
        if (preg_match('/cURL error ([0-9]*):/', $report['mesage'], $codeRaw)) {
            $exitcode = (int) $codeRaw[1];
        }
    }
} catch (\InvalidArgumentException $exc) {
    $report['mesage'] = $exc->getMessage();

    $exitcode = 4;
}

$engine->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');

exit($exitcode ?: ($written ? 0 : 2));
