# AGENTS.md — Sistem Informasi Keamanan Desa (SIKD)

Laravel 12, PHP 8.2+, SQLite (default), Tailwind CSS 4 + Vite, no Breeze/Jetstream.

## Setup & dev commands

```bash
composer setup                    # install + .env + key + migrate + npm build
composer dev                      # serve + queue:listen + pail + vite (concurrently)
php artisan migrate:fresh --seed  # rebuild DB with seed data
php artisan test                  # run all tests
composer test                     # clears config, then runs tests
./vendor/bin/pint                 # Laravel Pint (PSR-12) linter
```

## Roles & auth

- Three roles (seeded): `warga`, `perangkat`, `kades` — **no "satpam" role exists**
- Patrol officers are `warga` users assigned to a `PatrolSchedule` for today
- `User::hasRole(string|array $role)` checks `$this->role->name` (belongsTo Role)
- Role simulation: `/simulasi/switch/{role}` — **local/testing only**
- Login throttled: `throttle:5,1`; emergency button: `throttle:3,1`
- Default test accounts (after seed):
  - `warga{1..30}@desa.id` / `password`, `perangkat@desa.id`, `kades@desa.id`

## Report workflow

Status: `baru → diverifikasi → diproses → ditangani → selesai` (or `ditolak`)
- Warga report **auto-creates an Incident** and auto-assigns to today's active patrol warga
- Active patrol warga can process today's reports from others via `WargaController::prosesReport`
- Emergency/panic requires `latitude` + `longitude` (validated required)

## Routes & portals

| Portal | Prefix | Middleware |
|--------|--------|-----------|
| Public | `/` | guest |
| Warga | `/warga/*` | `role:warga` |
| Perangkat | `/perangkat/*` | `role:perangkat` |
| Kades | `/kades/*` | `role:kades` |

Patrol/ronda routes live under `/warga/ronda/*` (SatpamController), accessed by `warga` with today's active patrol schedule. Reports processing also under `/warga/laporan/*`.

Real-time updates API: `GET /reports/realtime-updates?last_id={id}` (returns today's reports for on-duty warga).

## Configuration quirks

- `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database` (sync in testing)
- `APP_TIMEZONE=Asia/Makassar`
- WhatsApp via Fonnte (`WA_GATEWAY_TOKEN`). Phone normalized to Indonesia `62` format.
- WebPush via `minishlink/web-push`. VAPID keys: `php artisan webpush:keys` — custom command with XAMPP OpenSSL workaround.

## Architecture

- `ActivityLog::log(string $activity, ?int $userId)` — audit trail helper
- `NotificationObserver` fires `WebPushService::sendPush()` + `WhatsAppService::sendNotification()` on creation
- Attachments use polymorphic `attachable_type` / `attachable_id`
- Patrol schedule status: `scheduled → completed` (or `missed`)
- Overlapping patrol validation checks ±1 day window
- Kades dashboard uses SQLite-compatible `strftime` for monthly aggregation
- `resources/css/app.css` uses Tailwind v4 `@import` syntax with custom `@theme` colors

## Tests

- All feature tests use `RefreshDatabase` — roles **must** be seeded in `setUp()` (use `Role::create` or `firstOrCreate`)
- DB is SQLite `:memory:` (see `phpunit.xml`)
- Key test files:
  - `SecuritySystemTest` — end-to-end workflow + edge cases
  - `PatrolDashboardTest` — patrol schedule visibility and report processing
  - `WebPushTest` — push subscription CRUD + observer trigger
  - `WhatsAppNotificationTest` — phone formatting, payload, error resilience
- Filter: `php artisan test --filter=TestName`

## Existing instruction files

- `.agents/rules/antigravity-rtk-rules.md` — RTK token-optimized CLI proxy; prefix commands with `rtk`
