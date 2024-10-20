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

require_once '../vendor/autoload.php';

const APP_NAME = 'RaiffeisenBankBalance';

/**
 * Get today's transactons list.
 */
$options = getopt('o::e::', ['output::environment::']);
Shared::init(['CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER'], \array_key_exists('environment', $options) ? $options['environment'] : '../.env');
$destination = \array_key_exists('output', $options) ? $options['output'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout');

ApiClient::checkCertificatePresence(Shared::cfg('CERT_FILE'), true);

$engine = new \Ease\Sand();

if (\Ease\Shared::cfg('APP_DEBUG', false)) {
    $engine->logBanner();
}

$apiInstance = new \VitexSoftware\Raiffeisenbank\PremiumAPI\GetAccountBalanceApi();
$xRequestId = time();

try {
    $balance = $apiInstance->getBalance($xRequestId, Shared::cfg('ACCOUNT_NUMBER'));
} catch (\Exception $e) {
    echo 'Exception when calling GetAccountBalanceApi->getBalance: ', $e->getMessage(), \PHP_EOL;
    $balance = ['message' => 'Exception when calling GetAccountBalanceApi->getBalance: ', $e->getMessage()];
}

$written = file_put_contents($destination, json_encode($balance, \Ease\Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));

$engine->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');

exit($written ? 0 : 1);
