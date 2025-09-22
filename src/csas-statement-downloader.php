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

\define('APP_NAME', 'CSas Statement Downloader');

if (\array_key_exists(1, $argv) && $argv[1] === '-h') {
    echo 'csas-statement-downloader <-dsave/to/directory> [-fformat] [-epath/to/.env]'.\PHP_EOL;
    echo \PHP_EOL.' supported formats: pdf, xml, xml-data, abo-standard, abo-internal, abo-standard-extended, abo-internal-extended, csv-comma, csv-semicolon, mt940';
    echo "\n";

    exit;
}

$options = getopt('o::e::d::f:', ['output::environment::destination::format']);
Shr::init(
    ['CSAS_API_KEY', 'CSAS_ACCESS_TOKEN'],
    \array_key_exists('environment', $options) ? $options['environment'] : (\array_key_exists('e', $options) ? $options['e'] : '../.env'),
);
$destination = \array_key_exists('output', $options) ? $options['output'] : (\array_key_exists('o', $options) ? $options['o'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout'));
$format = \array_key_exists('format', $options) ? $options['format'] : (\array_key_exists('f', $options) ? $options['f'] : \Ease\Shared::cfg('STATEMENT_FORMAT', 'pdf'));
$saveTo = \array_key_exists('destination', $options) ? $options['destination'] : (\array_key_exists('d', $options) ? $options['d'] : \Ease\Shared::cfg('STATEMENTS_DIR', getcwd()));

$apiInstance = new \SpojeNet\CSas\Accounts\DefaultApi(new \SpojeNet\CSas\ApiClient(
    [
        'apikey' => Shr::cfg('CSAS_API_KEY'),
        'token' => Shr::cfg('CSAS_ACCESS_TOKEN'),
        'debug' => Shr::cfg('CSAS_API_DEBUG', false),
        'sandbox' => Shr::cfg('CSAS_SANDBOX_MODE'),
    ],
));

$accountId = Shr::cfg('CSAS_ACCOUNT_UUID');
$accountIban = Shr::cfg('CSAS_ACCOUNT_IBAN');

if (empty($accountId)) {
    if ($accountIban) {
        // Map IBAN to account ID and get account object
        $account = Statementor::getAccountByIban($apiInstance, $accountIban);

        if (!$account) {
            $report['message'] = sprintf(_('Account not found for IBAN: %s'), Shr::cfg('CSAS_ACCOUNT_IBAN'));
            $exitcode = 5;

            throw new \InvalidArgumentException($report['message']);
        }

        $accountId = $account->getId();
    } else {
        $report['message'] = _('No CSAS_ACCOUNT_UUID or CSAS_ACCOUNT_IBAN provided');
        $exitcode = 1;
    }
}

$engine = new Statementor($accountId, $accountIban, Shr::cfg('CSAS_STATEMENT_SCOPE', 'last_month'));

if (Shr::cfg('APP_DEBUG', false)) {
    $engine->logBanner($engine->getAccountNumber().' '.$engine->getScopeSymbolic());
}

try {
    $status = 'ok';
    $exitcode = 0;
    $statements = $engine->getStatements($format);
} catch (ApiException $exc) {
    $status = $exc->getCode().': error';
    $exitcode = (int) $exc->getCode();
}

if (empty($statements) === false) {
    $engine->download($saveTo, $statements, $format);
} else {
    echo "no statements returned\n";

    // Generate empty/mock statement if enabled
    if (Shr::cfg('CSAS_GENERATE_EMPTY_STATEMENTS', false)) {
        $engine->addStatusMessage('Generating empty statement for period with no transactions', 'info');

        $generator = new \SpojeNet\CSas\Csas\EmptyStatementGenerator(
            $engine->getAccountNumber(),
            $accountIban,
            Shr::cfg('ACCOUNT_CURRENCY', 'CZK'),
            $engine->getSince(),
            $engine->getUntil(),
        );

        $mockStatements = $generator->generateEmptyStatements($format);

        if (!empty($mockStatements)) {
            $saved = [];

            foreach ($mockStatements as $statement) {
                $statementFilename = sprintf(
                    '0_%s_EMPTY_%s_%s_%s.%s',
                    $engine->getAccountNumber(),
                    $statement->accountStatementId,
                    Shr::cfg('ACCOUNT_CURRENCY', 'CZK'),
                    $statement->dateFrom->format('Y-m-d'),
                    $format,
                );

                $content = $generator->generateEmptyStatementContent($format, $statement);
                $filePath = $saveTo.'/'.$statementFilename;

                if (file_put_contents($filePath, $content)) {
                    $saved[$statementFilename] = $filePath;
                    $engine->addStatusMessage($statementFilename.' (empty statement) saved', 'success');
                }
            }

            $engine->addStatusMessage('Generated '.\count($saved).' empty statement(s)', 'info');
        }
    }
}

exit($exitcode);
