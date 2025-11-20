# Copilot Instructions for ČSAS Statement Tools

## Project Context
- **Purpose:** Tools for downloading and processing bank statements from ČSAS (Československá obchodní banka)
- **Integration:** Works with MultiFlexi framework for automated statement import
- **Technology:** PHP with ČSAS Open Banking API integration

## ⚠️ CRITICAL: ČSAS Token Management
- **Token Lifespan:** ČSAS access tokens expire after **only 5 minutes**
- **Before Development:** Always run `make token` to refresh access token
- **Authentication Errors:** Usually means token expired - refresh immediately
- **Token Location:** Stored in `.env` as `CSAS_ACCESS_TOKEN`

```bash
# Always refresh token before API work
make token

# Check token status
make check-token
```

## Schema Compliance
- **MultiFlexi JSON files** (`multiflexi/*.app.json`) must conform to:
  - https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json
- **Report files** must conform to:
  - https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.report.schema.json

## Code Quality Standards

### Language & Documentation
- **Comments:** English only
- **Messages:** English only (including errors)
- **Docblocks:** Required for all functions/classes (purpose, parameters, returns)
- **Documentation:** Markdown format
- **Testing:** Unit tests for all new/modified classes

### Development Best Practices
- **Variables:** Meaningful, descriptive names
- **Constants:** Replace magic numbers/strings
- **Exceptions:** Proper handling with meaningful messages
- **Type Safety:** Type hints for parameters and returns
- **Security:** Never expose sensitive banking data or tokens
- **Performance:** Optimize for API rate limits and token expiration
- **Compatibility:** Latest PHP and library versions

## Validation Process
- **PHP Syntax:** After every edit, **mandatory** run:
  ```bash
  php -l <filename>
  ```

## Development Workflow
```bash
# 1. Refresh ČSAS token (critical!)
make token

# 2. Test statement download
php src/statement-download.php

# 3. Validate configuration
multiflexi-cli application validate-json --file multiflexi/csas-tools.app.json

# 4. Run tests
vendor/bin/phpunit tests/
```

