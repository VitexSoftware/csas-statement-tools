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
$destination = \array_key_exists('output', $options) ? $options['output'] : (\array_key_exists('o', $options) ? $options['o'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout'));

// Keep your tokens fresh using https://github.com/Spoje-NET/csas-authorize.git

$engine = new \Ease\Sand();
$engine->setObjectName(Shr::cfg('CSAS_ACCOUNT_IBAN'));

if (Shr::cfg('APP_DEBUG', false)) {
    $engine->logBanner();
}

$apiInstance = new \SpojeNet\CSas\Accounts\DefaultApi(new \SpojeNet\CSas\ApiClient(
    [
        'apikey' => Shr::cfg('CSAS_API_KEY'),
        'token' => Shr::cfg('CSAS_ACCESS_TOKEN'),
        'debug' => Shr::cfg('CSAS_API_DEBUG', false),
        'sandbox' => Shr::cfg('CSAS_SANDBOX_MODE'),
    ],
));

function getAccountIDs(\SpojeNet\CSas\Accounts\DefaultApi $apiInstance): array
{
    $accounts = [];
    $accountsRaw = $apiInstance->getAccounts()->getAccounts();

    if (isset($accountsRaw) && \is_array($accountsRaw)) {
        foreach ($accountsRaw as $account) {
            $accounts[$account->getIdentification()->getIban()] = $account->getId();
        }
    }

    return $accounts;
}

// Helper function to map IBAN to account ID
function getAccountIdByIban(\SpojeNet\CSas\Accounts\DefaultApi $apiInstance, string $iban): ?string
{
    $accountsRaw = $apiInstance->getAccounts()->getAccounts();

    if (isset($accountsRaw) && \is_array($accountsRaw)) {
        foreach ($accountsRaw as $account) {
            if ($account->getIdentification()->getIban() === $iban) {
                return $account->getId();
            }
        }
    }

    return null;
}

function getAccountByIban(\SpojeNet\CSas\Accounts\DefaultApi $apiInstance, string $iban)
{
    $accountsRaw = $apiInstance->getAccounts()->getAccounts();

    if (isset($accountsRaw) && \is_array($accountsRaw)) {
        foreach ($accountsRaw as $account) {
            if ($account->getIdentification()->getIban() === $iban) {
                return $account;
            }
        }
    }

    return null;
}

try {
    // Map IBAN to account ID and get account object
    $account = getAccountByIban($apiInstance, Shr::cfg('CSAS_ACCOUNT_IBAN'));

    if (!$account) {
        throw new \InvalidArgumentException('Account not found for IBAN: '.Shr::cfg('CSAS_ACCOUNT_IBAN'));
    }

    $accountId = $account->getId();
    $balanceResponse = $apiInstance->getAccountBalance($accountId);

    // Compose output structure
    $result = [
        'numberPart2' => $account->getIdentification()->getOther(),
        'bankCode' => $account->getServicer()->getBankCode(),
        'currencyFolders' => [],
    ];

    // Group balances by currency
    $balancesByCurrency = [];
    $balances = method_exists($balanceResponse, 'getBalances') ? $balanceResponse->getBalances() : [];

    if (\is_array($balances)) {
        foreach ($balances as $balance) {
            $currency = $balance->getAmount()->getCurrency();
            $balanceType = $balance->getType()->getCodeOrProprietary()->getCode();
            $value = $balance->getAmount()->getValue();
            $status = method_exists($account, 'getStatus') && $account->getStatus() ? $account->getStatus() : 'ACTIVE';

            if (!isset($balancesByCurrency[$currency])) {
                $balancesByCurrency[$currency] = [
                    'currency' => $currency,
                    'status' => $status,
                    'balances' => [],
                ];
            }

            $balancesByCurrency[$currency]['balances'][] = [
                'balanceType' => $balanceType,
                'currency' => $currency,
                'value' => $value,
            ];
        }
    }

    $result['currencyFolders'] = array_values($balancesByCurrency);

    $written = file_put_contents($destination, json_encode($result, Shr::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
} catch (ApiException $exc) {
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
