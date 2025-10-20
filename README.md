# Simple Jellyfin Permission Manager

A clean, dependency-free PHP CLI tool to manage Jellyfin user permissions for **Jellyfin 10.10.7**.

**No Composer, no web server, no dependencies required!** - Just pure PHP.

## What It Does

Automatically updates permissions for all non-admin Jellyfin users by disabling:

- ❌ **EnableLiveTvAccess** - Allow Live TV access
- ❌ **EnableLiveTvManagement** - Allow Live TV recording management  
- ❌ **EnableVideoPlaybackTranscoding** - Allow video playback that requires transcoding
- ❌ **ForceRemoteSourceTranscoding** - Force transcoding of remote media sources

All other user permissions remain unchanged. Administrator accounts are automatically skipped for safety.

## Requirements

- PHP CLI (7.4 or higher)
- `allow_url_fopen` enabled in PHP (standard in most builds)
- Network access from this machine to your Jellyfin server
- A Jellyfin API key with admin rights

## Setup

1. **Copy the example configuration:**
   ```bash
   cp .env.example .env
   nano .env
   ```

2. **Set your Jellyfin details:**
   ```env
   JELLYFIN_URL=http://your-jellyfin-server:8096
   JELLYFIN_API_KEY=your_api_key_here
   ```

That's it! No installation, no dependencies needed.

## Usage

### Run Once
Simply execute the PHP script:
```bash
php update_policies.php
```

Or make it executable and run directly:
```bash
chmod +x update_policies.php
./update_policies.php
```

### Output Example
```json
{
    "success": true,
    "message": "Updated permissions for 12 users",
    "results": [
        {
            "userId": "abc123",
            "name": "alice",
            "status": "updated",
            "reason": "Successfully disabled Live TV access, Live TV management, Video transcoding, and Force remote transcoding"
        },
        {
            "userId": "def456",
            "name": "admin",
            "status": "skipped",
            "reason": "Administrator user - skipped for safety"
        }
    ],
    "timestamp": "2025-10-20 12:34:56"
}
```

## Automated Scheduling

To automatically keep new users compliant, schedule the script to run periodically using cron.

**Example:** Run daily at 3:30 AM
```cron
30 3 * * * cd /path/to/webhook-api && php update_policies.php >> /var/log/jellyfin-permissions.log 2>&1
```

**Example:** Run every 6 hours
```cron
0 */6 * * * cd /path/to/webhook-api && php update_policies.php >> /var/log/jellyfin-permissions.log 2>&1
```

## How It Works

The script performs these simple steps:

1. Loads Jellyfin URL and API key from `.env` file
2. Calls Jellyfin API to get all users: `GET /Users`
3. For each non-admin user:
   - Fetches current policy
   - Sets the 4 permissions to `false`
   - Updates via Jellyfin API: `POST /Users/{userId}/Policy`
4. Outputs JSON results with success/skip/error status for each user

**No web server needed** - it's just a simple command-line script!

## Troubleshooting

### Error: ".env file not found"
- Make sure you've created `.env` from `.env.example`
- Run the script from the correct directory

### Error: "Failed to connect to Jellyfin API"
- Verify `JELLYFIN_URL` is correct in your `.env` file
- Check network connectivity: `ping your-jellyfin-server`
- Ensure `allow_url_fopen` is enabled: `php -i | grep allow_url_fopen`
- Confirm your Jellyfin server is running

### Error: "Configuration error"
- Make sure both `JELLYFIN_URL` and `JELLYFIN_API_KEY` are set in `.env`
- Check for typos in variable names

### No users updated (all skipped)
- This is normal if all users are administrators
- Admins are skipped for safety to prevent lockouts

### Permission update not working
- Verify the API key has admin permissions in Jellyfin
- Check Jellyfin logs for API errors

## Project Structure

```
webhook-api/
├── update_policies.php    # Main CLI script
├── .env                   # Configuration file (create from .env.example)
├── .env.example           # Example configuration template
└── README.md              # This file
```

## Configuration Reference

| Variable | Required | Description | Example |
|----------|----------|-------------|---------|
| `JELLYFIN_URL` | Yes | Base URL to your Jellyfin server | `http://127.0.0.1:8096` or `https://jellyfin.example.com` |
| `JELLYFIN_API_KEY` | Yes | Jellyfin API key with admin rights | Get from Jellyfin Dashboard → API Keys |

## Security & Safety

**Safety features:**
- ✅ Admin users are automatically skipped and never modified
- ✅ Only 4 specific permissions are changed - everything else untouched
- ✅ Idempotent - safe to run multiple times
- ✅ No authentication needed - runs locally with direct API access

**Best practices:**
- Store `.env` file securely with restricted permissions: `chmod 600 .env`
- Keep API key secret - never commit `.env` to version control
- Test on a backup/staging Jellyfin server first
- Review output after running to verify expected results

## Technical Details

- **Tested with:** Jellyfin 10.10.7
- **PHP version:** 7.4+ (tested on 8.3)
- **API endpoints used:** 
  - `GET /Users` - List all users
  - `POST /Users/{userId}/Policy` - Update user policy
- **Authentication:** Uses `X-Emby-Token` header for Jellyfin API
- **Exit codes:** 
  - `0` - Success
  - `1` - Error (configuration, connection, or API failure)
- **Last updated:** 2025-10-20

## License

This project is provided as-is for managing Jellyfin user permissions.
