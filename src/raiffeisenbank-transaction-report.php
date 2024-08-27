<?php

/**
 * RaiffeisenBank - Transaction Reporter.
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.com>
 * @copyright  (C) 2024 Spoje.Net
 */

namespace SpojeNet\RaiffeisenBank;

use Ease\Shared;
use VitexSoftware\Raiffeisenbank\Statementor;
use VitexSoftware\Raiffeisenbank\ApiClient;

require_once('../vendor/autoload.php');

define('APP_NAME', 'RaiffeisenBank Statement Reporter');

$options = getopt("o::e::", ['output::environment::']);
Shared::init(['CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER'], array_key_exists('environment', $options) ? $options['environment'] : '../.env');
$destination = array_key_exists('output', $options) ? $options['output'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout');

ApiClient::checkCertificatePresence(Shared::cfg('CERT_FILE'), true);
$engine = new Statementor(Shared::cfg('ACCOUNT_NUMBER'));
$engine->setScope(Shared::cfg('STATEMENT_IMPORT_SCOPE', 'last_month'));

if (\Ease\Shared::cfg('APP_DEBUG', false)) {
    $engine->logBanner();
}
$engine->setScope(Shared::cfg('STATEMENT_IMPORT_SCOPE', 'yesterday'));
$statements = $engine->getStatements(Shared::cfg('ACCOUNT_CURRENCY', 'CZK'), Shared::cfg('STATEMENT_LINE', 'MAIN'));

$payments = [
    'source' => \Ease\Logger\Message::getCallerName($engine),
    'account' => Shared::cfg('ACCOUNT_NUMBER'),
    'in' => [],
    'out' => [],
    'in_total' => 0,
    'out_total' => 0,
    'in_sum_total' => 0,
    'out_sum_total' => 0,
    'from' => $engine->getSince()->format('Y-m-d'),
    'to' => $engine->getUntil()->format('Y-m-d')
];

if (empty($statements) === false) {
    $payments['status'] = "statement " . $statements[0]->statementId;
    foreach ($engine->download(sys_get_temp_dir(), $statements, 'xml') as $statement => $xmlFile) {
        // ISO 20022 XML to transaction array
        $statementArray = json_decode(json_encode(simplexml_load_file($xmlFile)), true);

        $payments['iban'] = $statementArray['BkToCstmrStmt']['Stmt']['Acct']['Id']['IBAN'];

        $entries = (array_key_exists('Ntry', $statementArray['BkToCstmrStmt']['Stmt']) ? (array_keys($statementArray['BkToCstmrStmt']['Stmt']['Ntry'])[0] == 0 ? $statementArray['BkToCstmrStmt']['Stmt']['Ntry'] : [$statementArray['BkToCstmrStmt']['Stmt']['Ntry']]) : []);
        foreach ($entries as $payment) {
            $payments[$payment['CdtDbtInd'] == 'CRDT' ? 'in' : 'out'][$payment['BookgDt']['DtTm']] = $payment['Amt'];
            $payments[$payment['CdtDbtInd'] == 'CRDT' ? 'in_sum_total' : 'out_sum_total'] += floatval($payment['Amt']);
            $payments[$payment['CdtDbtInd'] == 'CRDT' ? 'in_total' : 'out_total'] += 1;
        }
        unlink($xmlFile);
    }
} else {
    $payments['status'] = "no statements returned";
}

$written = file_put_contents($destination, json_encode($payments, \Ease\Shared::cfg('DEBUG') ? JSON_PRETTY_PRINT : 0));
$engine->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');
exit($written ? 0 : 1);
