# Webhook API for Jellyfin user policies# Simple Jellyfin Permission Manager



A tiny PHP API that talks to your Jellyfin server to:A clean, dependency-free PHP API to manage Jellyfin user permissions for **Jellyfin 10.10.7**.



- List all Jellyfin users (read-only)**No Composer or dependencies required!** - Just pure PHP.

- Update all non-admin users to disable specific permissions:

  - EnableLiveTvAccess## Setup

  - EnableLiveTvManagement

  - EnableVideoPlaybackTranscoding1. **Configure:**

  - ForceRemoteSourceTranscoding   ```bash

   cp .env.example .env

This is useful when you want to standardize user policies (e.g., disallow Live TV and transcoding) and re-apply them periodically for new users.   nano .env

   ```

## Requirements

2. **Set your Jellyfin details:**

- PHP with `allow_url_fopen` enabled and JSON support (standard in most PHP builds)   ```env

- Network access from this API host to your Jellyfin server   JELLYFIN_URL=http://your-jellyfin-server:8096

- A Jellyfin API key with permission to read users and update user policies (typically an admin key)   JELLYFIN_API_KEY=your_api_key_here

   API_SECRET=optional_webhook_secret

## Configuration   ```



Copy the example environment file and set your values:That's it! No `composer install` needed.



```## Usage

cp .env.example .env

```### Test Connection

```bash

Edit `.env`:curl -X POST http://localhost/webhook-api/ \

  -H "Content-Type: application/json" \

- `JELLYFIN_URL` (required) — Base URL to your Jellyfin server, e.g. `http://127.0.0.1:8096` or `https://jellyfin.example.com`  -d '{"action": "test_connection"}'

- `JELLYFIN_API_KEY` (required) — A Jellyfin API key with admin rights```

- `API_SECRET` (optional) — Reserved for adding request authentication; not used by the current code

### Update All User Permissions

## Endpoints```bash

curl -X POST http://localhost/webhook-api/ \

Base path below assumes this folder is served as `/webhook-api/` on your web server. Adjust to match your host/domain.  -H "Content-Type: application/json" \

  -d '{"action": "update_all_permissions"}'

- GET `/<base>/` — List users```

  - Returns an array of users from Jellyfin with a count and timestamp

  - Example: `/webhook-api/`### Get All Users

```bash

- GET `/<base>/?action=update-policies` — Update user policiescurl -X POST http://localhost/webhook-api/ \

  - Iterates over all users and updates non-admin users to disable the 4 permissions listed above  -H "Content-Type: application/json" \

  - Skips administrator accounts for safety  -d '{"action": "get_users"}'

  - Example: `/webhook-api/?action=update-policies````



- GET `/<base>/update_policies.php` — Same behavior as `?action=update-policies`## What It Does



Note: If your web server routes subpaths to `index.php`, requests whose path contains `update-policies` (e.g., `/webhook-api/update-policies`) will also trigger the update. Using the explicit query string `?action=update-policies` is the most portable/safe form.Disables these 4 permissions for all non-admin users:

- ❌ Allow Live TV access

## Usage examples- ❌ Allow Live TV recording management  

- ❌ Allow video playback that requires transcoding

Replace `http://your-host` with your server/address.- ❌ Force transcoding of remote media sources



List users:All other permissions remain unchanged.

```bash
curl -s http://your-host/webhook-api/
```

Update policies for non-admin users:

```bash
curl -s http://your-host/webhook-api/?action=update-policies
```

You should see a JSON summary similar to:

```json
{
  "success": true,
  "message": "Updated permissions for 12 users",
  "results": [
    {"userId": "...", "name": "alice", "status": "updated"},
    {"userId": "...", "name": "bob", "status": "skipped", "reason": "Administrator user - skipped for safety"}
  ],
  "timestamp": "2025-10-18 12:34:56"
}
```

## Security notes

- This API does not currently enforce authentication. If it is Internet-accessible, protect it at the web server level (e.g., IP allowlist, Basic Auth, or a reverse proxy ACL). The `API_SECRET` variable is included for future request authentication if you choose to add it.
- The update endpoint is idempotent for the four booleans being set to `false`—re-running it won’t re-enable anything.
- Admin users are intentionally skipped and left unchanged.

## Scheduling (optional)

To keep new users compliant automatically, you can schedule the update to run periodically. Example cron entry to run daily at 3:30 AM:

```cron
30 3 * * * curl -fsS http://your-host/webhook-api/?action=update-policies >/dev/null 2>&1
```

## Troubleshooting

- 500: Failed to connect to Jellyfin API
  - Check `JELLYFIN_URL` and that this server can reach Jellyfin (firewall/DNS)
  - Verify `allow_url_fopen` is enabled in PHP (or switch to cURL in code)
  - Confirm your Jellyfin API key is valid and has admin permissions
- 200 but no users returned
  - Ensure the API key has permission to list users
- SSL/HTTPS issues
  - If using a self-signed cert, ensure the PHP environment trusts it or use `http://` internally

## Project layout

- `index.php` — Main endpoint (lists users; updates when `?action=update-policies`)
- `update_policies.php` — Dedicated endpoint to perform the same update
- `.env` — Environment configuration (create from `.env.example`)

## Notes

- Date: 2025-10-18
- Tested against Jellyfin’s `/Users` and `/Users/{id}/Policy` endpoints using the `X-Emby-Token` header.
