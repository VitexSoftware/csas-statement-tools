# CSAS Zabbix Monitoring with Low Level Discovery

This directory contains Zabbix templates and scripts for monitoring multiple CSAS bank accounts using Zabbix's Low Level Discovery (LLD) functionality.

## Files

- `csas_balance_template.yaml` - Simple template for single account monitoring
- `csas_balance_template_lld.yaml` - **Low Level Discovery template for multiple accounts**
- `csas-zabbix-sender.php` - Helper script for account discovery and balance monitoring
- `README.md` - This documentation

## Setup Instructions

### 1. Import the LLD Template

1. Import `csas_balance_template_lld.yaml` into your Zabbix server
2. Apply the template to your target host(s)

### 2. Install Required Tools

Ensure `zabbix_sender` is installed on your monitoring host:

```bash
# Debian/Ubuntu
sudo apt install zabbix-sender

# RHEL/CentOS
sudo yum install zabbix-sender
```

### 3. Configure Environment

Make sure your `.env` file contains the required CSAS API credentials:

```bash
CSAS_API_KEY=your_api_key
CSAS_ACCESS_TOKEN=your_access_token
CSAS_ACCOUNT_UUID=your_account_uuid
CSAS_ACCOUNT_IBAN=your_account_iban
CSAS_SANDBOX_MODE=false
CSAS_API_DEBUG=false
```

## Usage

### Initial Account Discovery

Run the discovery script to find all available accounts:

```bash
cd zabbix/
./csas-zabbix-sender.php discover your-zabbix-host-name
```

This will:
- Query the CSAS API for all available accounts
- Send discovery data to Zabbix
- Zabbix will automatically create items and triggers for each discovered account

### Send Balance Data

For a specific account:

```bash
./csas-zabbix-sender.php balance ACCOUNT_ID your-zabbix-host-name
```

For all discovered accounts:

```bash
./csas-zabbix-sender.php all-balances your-zabbix-host-name
```

### Automation with Cron

Add to your crontab for automated monitoring:

```bash
# Discovery every day at 1 AM
0 1 * * * cd /path/to/csas-statement-tools/zabbix && ./csas-zabbix-sender.php discover your-host >/dev/null 2>&1

# Balance check every 15 minutes
*/15 * * * * cd /path/to/csas-statement-tools/zabbix && ./csas-zabbix-sender.php all-balances your-host >/dev/null 2>&1
```

## How It Works

### Low Level Discovery Process

1. **Discovery Rule**: The template contains a discovery rule `csas.accounts.discovery` that receives JSON data about available accounts

2. **Expected Discovery Data Format**:
   ```json
   {
     "data": [
       {
         "ACCOUNT_ID": "123456789",
         "ACCOUNT_NAME": "Main Account",
         "IBAN": "CZ1234567890123456789"
       },
       {
         "ACCOUNT_ID": "987654321", 
         "ACCOUNT_NAME": "Savings Account",
         "IBAN": "CZ9876543210987654321"
       }
     ]
   }
   ```

3. **Item Prototypes**: For each discovered account, Zabbix automatically creates:
   - `bank.balance.raw[{#ACCOUNT_ID}]` - Raw JSON balance data (trapper item)
   - `bank.balance.cz.CLB[{#ACCOUNT_ID}]` - Parsed CZK balance (dependent item)

4. **Trigger Prototypes**: For each account, creates a trigger that alerts when balance < 500 Kč

### Data Flow

```
CSAS API → csas-zabbix-sender.php → zabbix_sender → Zabbix Server → Items/Triggers
```

## Troubleshooting

### Check Zabbix Host Configuration

Ensure your Zabbix host is configured to accept trapper items:
- Host should be accessible from your monitoring system
- Template should be properly linked to the host

### Verify zabbix_sender

Test zabbix_sender manually:

```bash
echo "test" | zabbix_sender -z zabbix-server -s your-host -k test.key -i -
```

### Debug the Helper Script

Run with PHP error reporting:

```bash
php -d error_reporting=E_ALL ./csas-zabbix-sender.php discover your-host
```

### Check Discovery Data

Monitor the discovery rule in Zabbix:
- Go to Configuration → Hosts → Discovery
- Check the "Latest data" for the discovery rule
- Verify the JSON format matches expectations

## Key Differences from Simple Template

| Feature | Simple Template | LLD Template |
|---------|----------------|--------------|
| Accounts | Single account | Multiple accounts |
| Setup | Manual per account | Automatic discovery |
| Maintenance | Manual updates | Auto-discovery |
| Item Keys | Fixed: `bank.balance.raw` | Dynamic: `bank.balance.raw[{#ACCOUNT_ID}]` |
| Triggers | One trigger | One trigger per account |

## Benefits of LLD Approach

- **Scalable**: Automatically handles multiple accounts
- **Dynamic**: New accounts are discovered automatically
- **Maintainable**: No need to manually create items for each account
- **Consistent**: All accounts get the same monitoring setup
- **Flexible**: Easy to modify thresholds and monitoring parameters