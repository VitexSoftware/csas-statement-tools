# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Overview

CSas Statement Tools is a PHP-based collection of command-line tools for interacting with ČSas (Česká spořitelna) bank API to download statements, check balances, and generate transaction reports. The project uses the `spojenet/csas-accountsapi` library for bank API integration and follows PHP-FIG standards.

## Core Architecture

### Main Components
- **Statement Downloader** (`csas-statement-downloader.php`) - Downloads bank statements in PDF or ABO-standard format
- **Statement Mailer** (`csas-statement-mailer.php`) - Downloads and emails statements to recipients
- **Balance Checker** (`csas-balance.php`) - Retrieves current account balances in JSON format
- **Transaction Reporter** (`csas-transaction-report.php`) - Generates detailed transaction reports for specified periods
- **Empty Statement Generator** (`src/Csas/EmptyStatementGenerator.php`) - Generates mock statements for periods with no transactions

### Entry Points
All tools are accessible via:
- Direct PHP execution in `src/` directory
- System-wide binaries in `bin/` (shell wrappers that call PHP scripts in `/usr/lib/csas-statement-tools/`)
- MultiFlexi applications (containerized deployment)

### Configuration
- Environment-based configuration using `.env` files
- Required variables: `CSAS_API_KEY`, `CSAS_ACCESS_TOKEN`, `CSAS_ACCOUNT_IBAN`
- Optional: `CSAS_ACCOUNT_UUID`, `CSAS_SANDBOX_MODE`, various debug flags
- `CSAS_GENERATE_EMPTY_STATEMENTS=true` - Generate mock statements when no transactions are found
- See `.env.example` for complete configuration template

## Development Commands

### Setup
```bash
make vendor              # Install Composer dependencies
composer install         # Alternative to make vendor
```

### Code Quality
```bash
make cs                   # Fix coding standards using php-cs-fixer
make static-code-analysis # Run PHPStan analysis (level 6)
make tests               # Run PHPUnit tests (currently no test files exist)
```

### Testing Tools
```bash
make token               # Refresh CSAS access token
make balance            # Test balance checker
make statement-downloader # Test statement downloader
make statement-mailer    # Test statement mailer  
make report             # Test transaction reporter
```

### Docker/Container
```bash
make buildimage         # Build Docker image
make buildx            # Multi-platform Docker build
make drun              # Run containerized version with .env
```

## Code Standards

The project uses:
- **PHP CS Fixer** with Ergebnis configuration (PHP 8.1 rules)
- **PHPStan** level 6 analysis with baseline
- **PSR-12** coding standards
- Strict types declarations (`declare(strict_types=1)`)

All PHP files must include the standard license header block.

## MultiFlexi Integration

The project is designed as MultiFlexi applications with JSON configuration files in `multiflexi/`:
- Each tool has a corresponding `.multiflexi.app.json` configuration
- Environment variables are defined with types, descriptions, and validation
- Applications produce JSON artifacts for integration with other systems

### MultiFlexi Validation
All MultiFlexi JSON files must conform to the schema at:
https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json

Validate configurations using:
```bash
multiflexi-cli application validate-json --json multiflexi/[filename].app.json
```

## Empty Statement Generation

When no bank statements are available for a requested period, the tools can optionally generate mock "empty" statements:

- **Enable**: Set `CSAS_GENERATE_EMPTY_STATEMENTS=true` in environment
- **Implementation**: `SpojeNet\CSas\Csas\EmptyStatementGenerator` class in `src/Csas/`
- **Formats**: Supports all standard formats (PDF, ABO-standard, XML, etc.)
- **Content**: Contains account information and period dates with clear indication of no transactions
- **Filename**: Prefixed with `0_` and includes `EMPTY_` identifier (e.g., `0_CZ123456_EMPTY_abc123_CZK_2024-01-01.pdf`)
- **Use cases**: Automated reporting systems that require statement files even for periods with no activity
- **Email Support**: Statement mailer automatically handles empty statement generation and delivery
- **Autoloading**: Uses PSR-4 autoloading with proper namespace structure

## Authentication & Security

The tools use ČSas's OAuth2-based API authentication:
- Requires `CSAS_API_KEY` and `CSAS_ACCESS_TOKEN`
- Access tokens expire and must be refreshed (use `make token`)
- Supports sandbox mode for development (`CSAS_SANDBOX_MODE=true`)
- Certificate-based authentication for legacy operations (see related projects)

## Output Formats

### Balance Reports
JSON structure with currency-grouped balances:
```json
{
  "currencyFolders": [
    {
      "currency": "CZK",
      "status": "ACTIVE", 
      "balances": [
        {
          "balanceType": "CLAV",
          "currency": "CZK",
          "value": 48923.15
        }
      ]
    }
  ]
}
```

### Transaction Reports  
JSON with incoming/outgoing transaction summaries including totals and date ranges.

### Statement Downloads
- PDF format for human-readable statements
- ABO-standard format for machine processing
- Configurable date ranges using scope values (yesterday, current_month, last_month, etc.)

## Error Handling

Exit codes follow HTTP-style conventions:
- `0` - Success
- `1` - Certificate/configuration problems  
- `2` - File save errors
- `4xx` - Permission denied (API authentication issues)
- `5xx` - Server errors (API service problems)

## Related Projects

Part of the VitexSoftware banking tools ecosystem:
- `fiobank-statement-tools` - Similar tools for Fio Bank
- `raiffeisenbank-statement-tools` - Raiffeisenbank integration
- `kb-statement-tools` - Komerční banka tools