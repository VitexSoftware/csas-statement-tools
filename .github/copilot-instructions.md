<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

All code comments should be written in English.

All messages, including error messages, should be written in English.

When writing code, always include a docblock for functions and classes, describing their purpose, parameters, and return types.

When writing documentation, use Markdown format.

When writing commit messages, use the imperative mood and keep them concise.

When writing code comments, use complete sentences and proper grammar.

When writing code, always use meaningful variable names that describe their purpose.

When writing code, avoid using magic numbers or strings; instead, define constants for them.

When writing code, always handle exceptions properly and provide meaningful error messages.

When writing code, always include type hints for function parameters and return types.

When writing code, always ensure that it is secure and does not expose any sensitive information.

When writing code, always consider performance and optimize where necessary.

When writing code, always ensure that it is compatible with the latest version of PHP and the libraries we are using.

When writing code, always ensure that it is well-tested and includes unit tests where applicable.

When writing code, always ensure that it is maintainable and follows best practices.

When create new class or update existing class, always create or update its unittest test files.

All files in the multiflexi/*.app.json directory must conform to the schema available at: https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json

All produced reports must conform to the schema available at: https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.report.schema.json

⚠️ **CRITICAL ČSAS TOKEN MANAGEMENT**: ČSAS access tokens expire after only 5 minutes! Before any development, testing, or debugging involving ČSAS API calls, always run `make token` to refresh the access token. If authentication errors occur, the token has likely expired and must be refreshed immediately. The token is stored in `.env` as `CSAS_ACCESS_TOKEN`.
