# Simple Jellyfin Permission Manager# Webhook API for Jellyfin user policies# Simple Jellyfin Permission Manager



A clean, dependency-free PHP API to manage Jellyfin user permissions for **Jellyfin 10.10.7**.



**No Composer or dependencies required!** - Just pure PHP.A tiny PHP API that talks to your Jellyfin server to:A clean, dependency-free PHP API to manage Jellyfin user permissions for **Jellyfin 10.10.7**.



## Features



- List all Jellyfin users- List all Jellyfin users (read-only)**No Composer or dependencies required!** - Just pure PHP.

- Bulk update user permissions for non-admin users

- Disable specific permissions automatically:- Update all non-admin users to disable specific permissions:

  - Live TV access

  - Live TV recording management  - EnableLiveTvAccess## Setup

  - Video playback transcoding

  - Force remote transcoding  - EnableLiveTvManagement



This is useful when you want to standardize user policies (e.g., disallow Live TV and transcoding) and re-apply them periodically for new users.  - EnableVideoPlaybackTranscoding1. **Configure:**



## Requirements  - ForceRemoteSourceTranscoding   ```bash



- PHP with `allow_url_fopen` enabled and JSON support (standard in most PHP builds)   cp .env.example .env

- Network access from this API host to your Jellyfin server

- A Jellyfin API key with permission to read users and update user policies (typically an admin key)This is useful when you want to standardize user policies (e.g., disallow Live TV and transcoding) and re-apply them periodically for new users.   nano .env



## Setup   ```



1. **Copy the example configuration:**## Requirements

   ```bash

   cp .env.example .env2. **Set your Jellyfin details:**

   nano .env

   ```- PHP with `allow_url_fopen` enabled and JSON support (standard in most PHP builds)   ```env



2. **Set your Jellyfin details:**- Network access from this API host to your Jellyfin server   JELLYFIN_URL=http://your-jellyfin-server:8096

   ```env

   JELLYFIN_URL=http://your-jellyfin-server:8096- A Jellyfin API key with permission to read users and update user policies (typically an admin key)   JELLYFIN_API_KEY=your_api_key_here

   JELLYFIN_API_KEY=your_api_key_here

   ```   ```



That's it! No `composer install` needed.



## API EndpointsCopy the example environment file and set your values:That's it! No `composer install` needed.



Base path assumes this folder is served as `/webhook-api/` on your web server. Adjust to match your host/domain.



### GET `/` or `/index.php````## Usage

List all Jellyfin users.

cp .env.example .env

**Example:**

```bash```### Test Connection

curl -s http://your-host/webhook-api/

``````bash



**Response:**Edit `.env`:curl -X POST http://localhost/webhook-api/ \

```json

{  -H "Content-Type: application/json" \

  "success": true,

  "data": [...],- `JELLYFIN_URL` (required) — Base URL to your Jellyfin server, e.g. `http://127.0.0.1:8096` or `https://jellyfin.example.com`  -d '{"action": "test_connection"}'

  "count": 15,

  "timestamp": "2025-10-20 12:34:56",- `JELLYFIN_API_KEY` (required) — A Jellyfin API key with admin rights```

  "note": "Add ?action=update-policies to the URL to update user permissions"### Update All User Permissions

}

```## Endpoints```bash



### GET `/?action=update-policies`curl -X POST http://localhost/webhook-api/ \

Update user policies for all non-admin users.

Base path below assumes this folder is served as `/webhook-api/` on your web server. Adjust to match your host/domain.  -H "Content-Type: application/json" \

**Example:**

```bash  -d '{"action": "update_all_permissions"}'

curl -s http://your-host/webhook-api/?action=update-policies

```- GET `/<base>/` — List users```



**Response:**  - Returns an array of users from Jellyfin with a count and timestamp

```json

{  - Example: `/webhook-api/`### Get All Users

  "success": true,

  "message": "Updated permissions for 12 users",```bash

  "results": [

    {- GET `/<base>/?action=update-policies` — Update user policiescurl -X POST http://localhost/webhook-api/ \

      "userId": "abc123",

      "name": "alice",  - Iterates over all users and updates non-admin users to disable the 4 permissions listed above  -H "Content-Type: application/json" \

      "status": "updated",

      "reason": "Successfully disabled Live TV access, Live TV management, Video transcoding, and Force remote transcoding"  - Skips administrator accounts for safety  -d '{"action": "get_users"}'

    },

    {  - Example: `/webhook-api/?action=update-policies````

      "userId": "def456",

      "name": "admin",

      "status": "skipped",

      "reason": "Administrator user - skipped for safety"- GET `/<base>/update_policies.php` — Same behavior as `?action=update-policies`## What It Does

    }

  ],

  "timestamp": "2025-10-20 12:34:56"

}Note: If your web server routes subpaths to `index.php`, requests whose path contains `update-policies` (e.g., `/webhook-api/update-policies`) will also trigger the update. Using the explicit query string `?action=update-policies` is the most portable/safe form.Disables these 4 permissions for all non-admin users:

```

- ❌ Allow Live TV access

### GET `/update_policies.php`

Alternative endpoint that performs the same update operation as `?action=update-policies`.## Usage examples- ❌ Allow Live TV recording management  



**Example:**- ❌ Allow video playback that requires transcoding

```bash

curl -s http://your-host/webhook-api/update_policies.phpReplace `http://your-host` with your server/address.- ❌ Force transcoding of remote media sources

```



## What It Does

List users:All other permissions remain unchanged.

The API disables these 4 permissions for all non-admin users:

```bash

- ❌ **EnableLiveTvAccess** - Allow Live TV accesscurl -s http://your-host/webhook-api/

- ❌ **EnableLiveTvManagement** - Allow Live TV recording management  ```

- ❌ **EnableVideoPlaybackTranscoding** - Allow video playback that requires transcoding

- ❌ **ForceRemoteSourceTranscoding** - Force transcoding of remote media sourcesUpdate policies for non-admin users:



All other user permissions remain unchanged. Administrator accounts are automatically skipped for safety.```bash

curl -s http://your-host/webhook-api/?action=update-policies

## Security Notes```



⚠️ **Important:** This API does not currently enforce authentication. You should see a JSON summary similar to:



**Recommendations:**```json

- Protect it at the web server level with IP allowlist, Basic Auth, or reverse proxy ACL{

- Only expose it on internal networks  "success": true,

- Use firewall rules to restrict access  "message": "Updated permissions for 12 users",

  "results": [

**Safety features:**    {"userId": "...", "name": "alice", "status": "updated"},

- The update endpoint is idempotent - re-running it won't cause issues    {"userId": "...", "name": "bob", "status": "skipped", "reason": "Administrator user - skipped for safety"}

- Admin users are automatically skipped and never modified  ],

- Only the 4 specified permissions are changed  "timestamp": "2025-10-18 12:34:56"

}

## Automated Scheduling (Optional)```



To automatically keep new users compliant, schedule the update to run periodically using cron.## Security notes



**Example:** Run daily at 3:30 AM- This API does not currently enforce authentication. If it is Internet-accessible, protect it at the web server level (e.g., IP allowlist, Basic Auth, or a reverse proxy ACL).

```cron- The update endpoint is idempotent for the four booleans being set to `false`—re-running it won't re-enable anything.

30 3 * * * curl -fsS http://your-host/webhook-api/?action=update-policies >/dev/null 2>&1- Admin users are intentionally skipped and left unchanged.

```

## Scheduling (optional)

**Example:** Run every 6 hours

```cronTo keep new users compliant automatically, you can schedule the update to run periodically. Example cron entry to run daily at 3:30 AM:

0 */6 * * * curl -fsS http://your-host/webhook-api/?action=update-policies >/dev/null 2>&1

``````cron

30 3 * * * curl -fsS http://your-host/webhook-api/?action=update-policies >/dev/null 2>&1

## Troubleshooting```



### Error: "Failed to connect to Jellyfin API"## Troubleshooting

- Verify `JELLYFIN_URL` is correct

- Check network connectivity between this server and Jellyfin (firewall/DNS)- 500: Failed to connect to Jellyfin API

- Ensure `allow_url_fopen` is enabled in PHP  - Check `JELLYFIN_URL` and that this server can reach Jellyfin (firewall/DNS)

- Confirm your Jellyfin API key is valid and has admin permissions  - Verify `allow_url_fopen` is enabled in PHP (or switch to cURL in code)

  - Confirm your Jellyfin API key is valid and has admin permissions

### No users returned (empty list)- 200 but no users returned

- Ensure the API key has permission to list users  - Ensure the API key has permission to list users

- Check that the API key belongs to an administrator account- SSL/HTTPS issues

  - If using a self-signed cert, ensure the PHP environment trusts it or use `http://` internally

### SSL/HTTPS issues

- If using a self-signed certificate, ensure the PHP environment trusts it## Project layout

- For internal networks, consider using `http://` instead

- `index.php` — Main endpoint (lists users; updates when `?action=update-policies`)

### Permission update not working- `update_policies.php` — Dedicated endpoint to perform the same update

- Verify the API key has permission to modify user policies- `.env` — Environment configuration (create from `.env.example`)

- Check that you're not trying to modify admin users (they're skipped by design)

## Notes

## Project Structure

- Date: 2025-10-18

```- Tested against Jellyfin’s `/Users` and `/Users/{id}/Policy` endpoints using the `X-Emby-Token` header.

webhook-api/
├── index.php              # Main endpoint (list users + update with ?action=update-policies)
├── update_policies.php    # Dedicated update endpoint
├── .env                   # Configuration file (create from .env.example)
├── .env.example           # Example configuration template
└── README.md              # This file
```

## Configuration Reference

| Variable | Required | Description | Example |
|----------|----------|-------------|---------|
| `JELLYFIN_URL` | Yes | Base URL to your Jellyfin server | `http://127.0.0.1:8096` or `https://jellyfin.example.com` |
| `JELLYFIN_API_KEY` | Yes | Jellyfin API key with admin rights | Your API key from Jellyfin Dashboard → API Keys |

## Technical Details

- **Tested with:** Jellyfin 10.10.7
- **API endpoints used:** 
  - `GET /Users` - List all users
  - `POST /Users/{userId}/Policy` - Update user policy
- **Authentication:** Uses `X-Emby-Token` header for Jellyfin API authentication
- **Last updated:** 2025-10-20

## License

This project is provided as-is for managing Jellyfin user permissions.
