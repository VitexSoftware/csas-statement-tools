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

namespace SpojeNet\CSas\Csas;

/**
 * Helper class for generating empty/mock bank statements.
 */
class EmptyStatementGenerator extends \Ease\Sand
{
    use \Ease\Logger\Logging;
    private string $accountNumber;
    private string $accountIban;
    private string $currency;
    private \DateTime $dateFrom;
    private \DateTime $dateTo;

    public function __construct(string $accountNumber, string $accountIban, string $currency, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $this->accountNumber = $accountNumber;
        $this->accountIban = $accountIban;
        $this->currency = $currency;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->setObjectName('EmptyStatementGenerator@'.$accountNumber);
    }

    /**
     * Generate mock statement objects for empty periods.
     *
     * @param string $format The statement format (pdf, abo-standard, xml, etc.)
     *
     * @return array<object> Array of mock statement objects
     */
    public function generateEmptyStatements(string $format): array
    {
        $mockStatements = [];

        // Create a mock statement object that mimics the real statement structure
        $mockStatement = new \stdClass();
        $mockStatement->accountStatementId = 'EMPTY_'.uniqid();
        $mockStatement->year = (int) $this->dateFrom->format('Y');
        $mockStatement->month = (int) $this->dateFrom->format('m');
        $mockStatement->sequenceNumber = 0; // Use 0 for empty statements
        $mockStatement->period = $this->dateFrom->format('Y-m');
        $mockStatement->dateFrom = $this->dateFrom;
        $mockStatement->dateTo = $this->dateTo;
        $mockStatement->formats = [$format];
        $mockStatement->isEmpty = true; // Mark as empty statement

        $mockStatements[] = $mockStatement;

        return $mockStatements;
    }

    /**
     * Generate empty statement content in specified format.
     *
     * @param string $format    The statement format
     * @param object $statement Mock statement object
     *
     * @return string Generated empty statement content
     */
    public function generateEmptyStatementContent(string $format, object $statement): string
    {
        switch (strtolower($format)) {
            case 'abo-standard':
                return $this->generateEmptyAboStatement($statement);
            case 'xml':
                return $this->generateEmptyXmlStatement($statement);
            case 'pdf':
                return $this->generateEmptyPdfStatement($statement);

            default:
                return $this->generateEmptyTextStatement($statement);
        }
    }

    /**
     * Generate empty ABO-standard format statement.
     */
    private function generateEmptyAboStatement(object $statement): string
    {
        $dateFrom = $statement->dateFrom->format('dmy');
        $dateTo = $statement->dateTo->format('dmy');

        // ABO-standard header record (074)
        return sprintf(
            "074%s%-40s%s%s%s%s%s%03d%s%s\n",
            str_pad($this->accountNumber, 16, '0', \STR_PAD_LEFT),
            'NO TRANSACTIONS - EMPTY PERIOD',
            $dateFrom,
            str_pad('0', 12, '0'), // Opening balance
            '+',
            str_pad('0', 15, '0'), // Total debits
            '+',
            0, // Number of records
            $dateTo,
            str_repeat(' ', 14),
        );
    }

    /**
     * Generate empty XML format statement (ISO 20022).
     */
    private function generateEmptyXmlStatement(object $statement): string
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Create root element
        $document = $xml->createElement('Document');
        $document->setAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:camt.053.001.02');
        $xml->appendChild($document);

        // Bank to Customer Statement
        $bkToCstmrStmt = $xml->createElement('BkToCstmrStmt');
        $document->appendChild($bkToCstmrStmt);

        // Group Header
        $grpHdr = $xml->createElement('GrpHdr');
        $bkToCstmrStmt->appendChild($grpHdr);

        $msgId = $xml->createElement('MsgId', 'EMPTY-'.$statement->accountStatementId);
        $grpHdr->appendChild($msgId);

        $creDtTm = $xml->createElement('CreDtTm', date('c'));
        $grpHdr->appendChild($creDtTm);

        // Statement
        $stmt = $xml->createElement('Stmt');
        $bkToCstmrStmt->appendChild($stmt);

        $id = $xml->createElement('Id', $statement->accountStatementId);
        $stmt->appendChild($id);

        $elctrncSeqNb = $xml->createElement('ElctrncSeqNb', (string) $statement->sequenceNumber);
        $stmt->appendChild($elctrncSeqNb);

        $creDtTm = $xml->createElement('CreDtTm', date('c'));
        $stmt->appendChild($creDtTm);

        // From/To dates
        $frToDt = $xml->createElement('FrToDt');
        $stmt->appendChild($frToDt);

        $frDtTm = $xml->createElement('FrDtTm', $statement->dateFrom->format('c'));
        $frToDt->appendChild($frDtTm);

        $toDtTm = $xml->createElement('ToDtTm', $statement->dateTo->format('c'));
        $frToDt->appendChild($toDtTm);

        // Account
        $acct = $xml->createElement('Acct');
        $stmt->appendChild($acct);

        $acctId = $xml->createElement('Id');
        $acct->appendChild($acctId);

        $iban = $xml->createElement('IBAN', $this->accountIban);
        $acctId->appendChild($iban);

        $ccy = $xml->createElement('Ccy', $this->currency);
        $acct->appendChild($ccy);

        // Balance (opening and closing should be the same for empty statement)
        $bal = $xml->createElement('Bal');
        $stmt->appendChild($bal);

        $tp = $xml->createElement('Tp');
        $bal->appendChild($tp);
        $cdOrPrtry = $xml->createElement('CdOrPrtry');
        $tp->appendChild($cdOrPrtry);
        $cd = $xml->createElement('Cd', 'OPBD');
        $cdOrPrtry->appendChild($cd);

        $amt = $xml->createElement('Amt', '0.00');
        $amt->setAttribute('Ccy', $this->currency);
        $bal->appendChild($amt);

        $cdtDbtInd = $xml->createElement('CdtDbtInd', 'DBIT');
        $bal->appendChild($cdtDbtInd);

        $dt = $xml->createElement('Dt');
        $bal->appendChild($dt);
        $dtTm = $xml->createElement('DtTm', $statement->dateFrom->format('c'));
        $dt->appendChild($dtTm);

        // Add note about empty statement
        $addtlStmtInf = $xml->createElement('AddtlStmtInf', 'No transactions for the specified period.');
        $stmt->appendChild($addtlStmtInf);

        return $xml->saveXML();
    }

    /**
     * Generate empty PDF statement content (placeholder).
     */
    private function generateEmptyPdfStatement(object $statement): string
    {
        // For PDF, we'll generate a simple text that explains this is an empty statement
        // In a real implementation, you might want to use a PDF library like TCPDF or FPDF
        return sprintf(
            "BANK STATEMENT - NO TRANSACTIONS\n\n".
            "Account: %s\n".
            "IBAN: %s\n".
            "Currency: %s\n".
            "Period: %s to %s\n\n".
            "No transactions were found for this period.\n".
            "This is an automatically generated empty statement.\n\n".
            "Generated: %s\n",
            $this->accountNumber,
            $this->accountIban,
            $this->currency,
            $statement->dateFrom->format('Y-m-d'),
            $statement->dateTo->format('Y-m-d'),
            date('Y-m-d H:i:s'),
        );
    }

    /**
     * Generate empty text format statement.
     */
    private function generateEmptyTextStatement(object $statement): string
    {
        return sprintf(
            "Empty Statement\nAccount: %s\nPeriod: %s - %s\nNo transactions found.\n",
            $this->accountNumber,
            $statement->dateFrom->format('Y-m-d'),
            $statement->dateTo->format('Y-m-d'),
        );
    }
}
