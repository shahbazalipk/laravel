# Event Operations MCP

This Streamable HTTP MCP server exposes read-only, event-scoped operational data for integrations such as n8n.

## Production setup

Run the migration:

```bash
php artisan migrate
```

Create a token for event `25`:

```bash
php artisan mcp:token:create 25 --name=n8n --ability=registrations.read
```

The command prints the plaintext token once. Store it in the n8n credential store, not in a workflow or source control.

Configure the MCP client:

- URL: `https://your-event-domain.example/mcp/event-operations`
- Transport: Streamable HTTP
- Header: `Authorization: Bearer <token>`

You can also open **Settings → MCP** in the event admin to copy the URL and generate or revoke tokens for the current event.

The token fixes both the event and organization scope. Clients cannot supply or override those IDs.

## Available tools

- `registrations_summary`: totals grouped by workflow status, category, payment status, and check-in state
- `get_registration_status`: one registration's workflow, payment, and check-in status
- `payment_summary`: ledger-derived payment counts and amounts by currency
- `recent_registrations`: recent operational registration records without contact details
- `checkin_summary`: check-in totals, rate, today count, and category breakdown

## Adding another read action

1. Put reusable query logic in `app/Mcp/Services`.
2. Create a tool in `app/Mcp/Tools` that extends `ReadOnlyEventTool`.
3. Give it an explicit stable `Name`, a `Description`, and read-only/idempotent annotations.
4. Define and validate a bounded input schema.
5. Register the tool in `EventOperationsServer::$tools`.
6. Add tenant-isolation, authorization, response, and audit assertions to the MCP feature test.

Use a new ability for another data domain by overriding `requiredAbility()` in the tool. Avoid arbitrary SQL tools and return only the minimum necessary personal data.

## Security controls

- SHA-256 hashed bearer tokens; plaintext tokens are never stored
- expiration and revocation support
- event and organization scope derived only from the token
- per-token abilities
- request rate limiting
- immutable tool audit records
- no attendee contact details in the initial tool set
