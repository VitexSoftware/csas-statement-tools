ČSas Statement Tools
====================

A set of tools for downloading and subsequent operations with ČSas (Česká spořitelna) bank statements

[![View csas-statement-tools on GitHub](https://img.shields.io/github/stars/VitexSoftware/csas-statement-tools?color=232323&label=csas-statement-tools&logo=github&labelColor=232323)](https://github.com/Vitexsoftware/csas-statement-tools) 
[![Author Spoje-NET](https://img.shields.io/badge/VitexSoftware-b820f9?labelColor=b820f9&logo=githubsponsors&logoColor=fff)](https://github.com/VitexSoftware) ![Written in PHP](https://img.shields.io/static/v1?label=&message=PHP&color=777BB4&logo=php&logoColor=FFFFFF)

Statement Downloader
--------------------

![csas-statement-downloader](csas-statement-downloader.svg?raw=true)

Download bank statements for the required period in the required format to the specified or current folder

```shell
csas-statement-downloader [save/to/directory] [format] [path/to/.env]
```

Example output when EASE_LOGGER=console

```
12/01/2023 16:37:10 ⚙ ❲Csas Statement Downloader⦒123456789@VitexSoftware\Csas\Statementor❳ Request statements from 2023-11-30 to 2023-11-30
12/01/2023 16:37:13 🌼 ❲Csas Statement Downloader⦒123@VitexSoftware\Csas\Statementor❳ 10_2023_123_3780381_CZK_2023-11-01.xml saved
12/01/2023 16:37:13 ℹ ❲Csas Statement Downloader⦒123456789@VitexSoftware\Csas\Statementor❳ Download done. 1 of 1 saved

```

Statement mailer
----------------

![csas-statement-mailer](csas-statement-mailer.svg?raw=true)

Download bank statements for the required period in the required format and send it by email

```shell
csas-statement-mailer <recipient@domain,[recipient1@domain,...]> [format] [path/to/.env]
```

Balance Check
-------------

![csas-balance](csas-balance.svg?raw=true)

```shell
csas-balance [-opath/to/.env]
```

Example output:

```json
{
    "balances": [
        {
            "type": {
                "codeOrProprietary": {
                    "code": "CLAV"
                }
            },
            "amount": {
                "value": 48923.15,
                "currency": "CZK"
            },
            "creditDebitIndicator": "DBIT",
            "date": {
                "dateTime": "2017-02-17T12:32:41+00:00"
            }
        }
    ]
}
```

Transaction Report
------------------

![csas-transaction-report](csas-transaction-report.svg?raw=true)

```shell
csas-transaction-report --output="/tmp/transaction_report.json" [--environment="path/to/.env"]
```
(the default output is stdout)

>> Note: The Chosen `STATEMENT_LINE` must support statement frequency for `REPORT_SCOPE` 

Example output:

```json
{
    "source": "000000000@VitexSoftware\\Csas\\Statementor",
    "account": "000000000",
    "in": {
        "2024-03-06T11:17:34": "2904",
        "2024-03-07T06:02:04": "19602",
        "2024-03-12T11:29:04": "363",
        "2024-03-12T12:55:05": "363",
        "2024-03-15T10:29:36": "26892.25",
        "2024-03-19T01:11:40": "1815",
        "2024-03-19T05:25:52": "726",
        "2024-03-19T15:05:42": "3630",
        "2024-03-21T17:56:34": "77621.5",
        "2024-03-22T06:02:01": "1996.5",
        "2024-03-25T10:26:12": "2359.5",
        "2024-03-25T12:52:38": "5989.5",
        "2024-03-27T17:59:30": "3085.5",
        "2024-02-02T11:26:26": "12342",
        "2024-02-06T14:05:14": "363",
        "2024-02-06T14:06:09": "181.5",
        "2024-02-12T13:45:10": "1452",
        "2024-02-13T01:16:16": "275.88",
        "2024-02-13T08:13:16": "181.5",
        "2024-02-14T11:55:21": "968",
        "2024-02-14T15:51:04": "1694",
        "2024-02-14T16:35:02": "10527",
        "2024-02-15T01:25:24": "2178",
        "2024-02-15T19:26:15": "1058.75",
        "2024-02-15T19:26:40": "1270.5",
        "2024-02-17T18:59:54": "726",
        "2024-02-20T11:52:17": "907.5",
        "2024-02-22T11:54:16": "2359.5",
        "2024-02-25T20:27:46": "12069.75",
        "2024-02-27T17:18:38": "7018",
        "2024-02-29T01:32:39": "7199.5"
    },
    "out": {
        "2024-03-01T15:09:43": "12000",
        "2024-03-01T15:10:12": "16000",
        "2024-03-08T11:52:50": "46222",
        "2024-03-13T06:06:37": "2928",
        "2024-03-15T06:16:36": "632",
        "2024-03-15T13:37:10": "20000",
        "2024-03-16T06:17:31": "399",
        "2024-03-17T06:22:15": "2654",
        "2024-03-20T06:08:20": "2552",
        "2024-03-20T15:25:44": "1367",
        "2024-03-25T16:56:26": "21222",
        "2024-03-26T20:42:37": "2520.13",
        "2024-03-26T20:55:40": "181.5",
        "2024-03-26T20:57:12": "16692",
        "2024-03-26T20:58:46": "10000",
        "2024-03-26T21:00:27": "3375",
        "2024-03-26T21:01:29": "6380",
        "2024-03-26T21:02:10": "3038",
        "2024-03-26T21:03:51": "23037",
        "2024-03-26T21:04:44": "4200",
        "2024-03-26T21:05:44": "17700",
        "2024-03-26T21:06:34": "800",
        "2024-03-26T21:10:06": "16692",
        "2024-03-26T21:11:02": "10000",
        "2024-03-26T21:11:50": "3375",
        "2024-03-26T21:12:34": "6380",
        "2024-03-26T21:13:12": "3038",
        "2024-03-31T23:59:59": "63.55",
        "2024-02-02T18:06:22": "20000",
        "2024-02-10T01:09:24": "24100",
        "2024-02-21T11:24:38": "50000",
        "2024-02-29T23:59:59": "59.45"
    },
    "in_total": 34,
    "out_total": 34,
    "in_sum_total": 313035.22000000003,
    "out_sum_total": 347705.63,
    "from": "2024-02-01",
    "to": "2024-03-31",
    "iban": "CZ0000000000000000000000"
}
```

Exit Codes
----------

0: success - report sent
1: certificate file problem
2: error saving report
4xx: Permission Denied
5xx: Server error

Empty Statement Generation
--------------------------

When no bank statement is available for a requested period, you can optionally generate a mock statement:

* **Enable**: Set `CSAS_GENERATE_EMPTY_STATEMENTS=true` in your environment
* **Supported Formats**: PDF, ABO-standard, XML, and other available formats
* **File Naming**: Files are prefixed with `0_` and include `EMPTY_` identifier (e.g., `0_CZ123456_EMPTY_abc123_CZK_2024-01-01.pdf`)
* **Content**: Contains account information, period dates, and clear indication that no transactions occurred
* **Use Cases**: Automated reporting systems that require statement files even for periods with no banking activity
* **Email Support**: Statement mailer also supports sending empty statements when enabled

Configuration
-------------

Please set this environment variables or specify path to .env file

```env
# ČSas API Configuration
CSAS_API_KEY=your_api_key_here
CSAS_ACCESS_TOKEN=your_access_token_here
CSAS_ACCOUNT_IBAN=CZ4108000000000782553098
CSAS_ACCOUNT_UUID=optional_account_uuid

# Optional settings
CSAS_SANDBOX_MODE=false
CSAS_API_DEBUG=false
ACCOUNT_CURRENCY=CZK
STATEMENT_FORMAT=pdf
STATEMENTS_DIR=~/Documents/
APP_DEBUG=false
EASE_LOGGER=syslog|eventlog|console

# Empty statement generation
CSAS_GENERATE_EMPTY_STATEMENTS=false

# Mailer specific
STATEMENTS_TO=statement@recipient.com
STATEMENTS_FROM=email@address.com
STATEMENTS_REPLYTO=email@address.com
STATEMENTS_CC=email@address.com
```

 >> Format Note: only `pdf` and `abo-standard` work for us. Please [ask the support for explanation](https://developers.erstegroup.com/support)

Availble Import Scope Values

----------------------------

* 'yesterday'
* 'current_month'
* 'last_month'
* 'last_two_months'
* 'previous_month'
* 'two_months_ago'
* 'this_year'
* 'January'
* 'February'
* 'March'
* 'April'
* 'May'
* 'June'
* 'July'
* 'August'
* 'September'
* 'October'
* 'November',
* 'December'

Se The List of [statement formats provided](https://jsapi.apiary.io/apis/eahaccountsapiv3prod/reference/statements/list-of-statements/get-statements-list.html)

Created using the library [php-csas-accountsapi](https://github.com/Spoje-NET/php-csas-accountsapi)

MultiFlexi
----------

**Csas Statement Tools** is ready for run as [MultiFlexi](https://multiflexi.eu) application.
See the full list of ready-to-run applications within the MultiFlexi platform on the [application list page](https://www.multiflexi.eu/apps.php).

[![MultiFlexi App](https://github.com/VitexSoftware/MultiFlexi/blob/main/doc/multiflexi-app.svg)](https://www.multiflexi.eu/apps.php)

### MultiFlexi JSON Schema Compliance

All MultiFlexi application definitions have been updated to comply with the latest schema requirements:

- **Artifacts Structure**: Changed from object format to array format as required by MultiFlexi 2.0.0 schema
- **Environment Variable Types**: Updated `STATEMENT_FORMAT` type from deprecated `"select"` to compliant `"set"` type
- **Schema Validation**: All JSON files now pass strict schema validation using `multiflexi-cli application validate-json`

The following applications are available:
- `cs_balance.multiflexi.app.json` - Balance checking application
- `cs_statement_downloader.multiflexi.app.json` - Statement download application  
- `cs_statement_mailer.multiflexi.app.json` - Statement email delivery application
- `cs_transaction_report.multiflexi.app.json` - Transaction reporting application

Installation
------------

The repository with packages for Debian & Ubuntu is availble:

```shell
sudo apt install lsb-release wget apt-transport-https bzip2
wget -qO- https://repo.vitexsoftware.com/keyring.gpg | sudo tee /etc/apt/trusted.gpg.d/vitexsoftware.gpg
echo "deb [signed-by=/etc/apt/trusted.gpg.d/vitexsoftware.gpg]  https://repo.vitexsoftware.com  $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/vitexsoftware.list
sudo apt update
sudo apt install csas-statement-tools
```

See Also
--------

* [fiobank-statement-tools](https://github.com/Spoje-NET/fiobank-statement-tools)
* [raiffeisenbank-statement-tools](https://github.com/Spoje-NET/raiffeisenbank-statement-tools)
* [kb-statement-tools](https://github.com/Spoje-NET/kb-statement-tools)

## Exit Codes

Applications in this package use the following exit codes:

- `0`: Success
- `1`: General error
- `5`: Application-specific error
