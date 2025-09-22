#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * CSAS Zabbix Sender Script
 * 
 * This script handles:
 * 1. Account discovery for Zabbix LLD
 * 2. Sending balance data for multiple accounts
 * 
 * Usage:
 *   php csas-zabbix-sender.php discover [zabbix_host]
 *   php csas-zabbix-sender.php balance [account_id] [zabbix_host]
 */

require_once '../vendor/autoload.php';

use Ease\Shared as Shr;

if ($argc < 2) {
    echo "Usage:\n";
    echo "  Discovery: {$argv[0]} discover [zabbix_host]\n";
    echo "  Balance:   {$argv[0]} balance [account_id] [zabbix_host]\n";
    exit(1);
}

$command = $argv[1];
$zabbixHost = $argv[2] ?? 'localhost';

// Initialize configuration
Shr::init(['CSAS_API_KEY', 'CSAS_ACCESS_TOKEN'], '../.env');

/**
 * Send data to Zabbix using zabbix_sender
 */
function sendToZabbix(string $host, string $key, string $value, string $zabbixServer = 'localhost'): bool
{
    $cmd = sprintf(
        'zabbix_sender -z %s -s %s -k %s -o %s',
        escapeshellarg($zabbixServer),
        escapeshellarg($host),
        escapeshellarg($key),
        escapeshellarg($value)
    );
    
    exec($cmd, $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✓ Sent to Zabbix: $key\n";
        return true;
    } else {
        echo "✗ Failed to send to Zabbix: $key\n";
        echo implode("\n", $output) . "\n";
        return false;
    }
}

/**
 * Get list of available accounts
 */
function getAccounts(): array
{
    $apiInstance = new \SpojeNet\CSas\Accounts\DefaultApi(new \SpojeNet\CSas\ApiClient([
        'apikey' => Shr::cfg('CSAS_API_KEY'),
        'token' => Shr::cfg('CSAS_ACCESS_TOKEN'),
        'debug' => Shr::cfg('CSAS_API_DEBUG', false),
        'sandbox' => Shr::cfg('CSAS_SANDBOX_MODE'),
    ]));

    try {
        $accountList = $apiInstance->getAccountList();
        $accounts = [];
        
        foreach ($accountList->getAccounts() as $account) {
            $accounts[] = [
                'ACCOUNT_ID' => $account->getId(),
                'ACCOUNT_NAME' => $account->getAlias() ?: $account->getId(),
                'IBAN' => $account->getIban()
            ];
        }
        
        return $accounts;
    } catch (Exception $e) {
        echo "Error getting accounts: " . $e->getMessage() . "\n";
        return [];
    }
}

/**
 * Get balance for specific account
 */
function getAccountBalance(string $accountId): ?string
{
    $apiInstance = new \SpojeNet\CSas\Accounts\DefaultApi(new \SpojeNet\CSas\ApiClient([
        'apikey' => Shr::cfg('CSAS_API_KEY'),
        'token' => Shr::cfg('CSAS_ACCESS_TOKEN'),
        'debug' => Shr::cfg('CSAS_API_DEBUG', false),
        'sandbox' => Shr::cfg('CSAS_SANDBOX_MODE'),
    ]));

    try {
        $balance = $apiInstance->getAccountBalance($accountId);
        return json_encode($balance, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        echo "Error getting balance for account $accountId: " . $e->getMessage() . "\n";
        return null;
    }
}

// Handle commands
switch ($command) {
    case 'discover':
        echo "Discovering CSAS accounts...\n";
        $accounts = getAccounts();
        
        if (empty($accounts)) {
            echo "No accounts found.\n";
            exit(1);
        }
        
        $discoveryData = json_encode(['data' => $accounts], JSON_PRETTY_PRINT);
        echo "Discovery data:\n$discoveryData\n\n";
        
        // Send discovery data to Zabbix
        if (sendToZabbix($zabbixHost, 'csas.accounts.discovery', $discoveryData)) {
            echo "Discovery data sent successfully!\n";
        } else {
            exit(1);
        }
        break;
        
    case 'balance':
        if ($argc < 3) {
            echo "Account ID required for balance command.\n";
            echo "Usage: {$argv[0]} balance [account_id] [zabbix_host]\n";
            exit(1);
        }
        
        $accountId = $argv[2];
        $zabbixHost = $argv[3] ?? 'localhost';
        
        echo "Getting balance for account: $accountId\n";
        $balanceData = getAccountBalance($accountId);
        
        if ($balanceData === null) {
            echo "Failed to get balance data.\n";
            exit(1);
        }
        
        echo "Balance data:\n$balanceData\n\n";
        
        // Send balance data to Zabbix
        $key = "bank.balance.raw[$accountId]";
        if (sendToZabbix($zabbixHost, $key, $balanceData)) {
            echo "Balance data sent successfully!\n";
        } else {
            exit(1);
        }
        break;
        
    case 'all-balances':
        echo "Getting balances for all accounts...\n";
        $accounts = getAccounts();
        
        if (empty($accounts)) {
            echo "No accounts found.\n";
            exit(1);
        }
        
        foreach ($accounts as $account) {
            $accountId = $account['ACCOUNT_ID'];
            echo "\nProcessing account: {$account['ACCOUNT_NAME']} ($accountId)\n";
            
            $balanceData = getAccountBalance($accountId);
            if ($balanceData !== null) {
                $key = "bank.balance.raw[$accountId]";
                sendToZabbix($zabbixHost, $key, $balanceData);
            }
            
            // Small delay between requests
            sleep(1);
        }
        break;
        
    default:
        echo "Unknown command: $command\n";
        echo "Available commands: discover, balance, all-balances\n";
        exit(1);
}