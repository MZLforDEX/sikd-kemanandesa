# AGENTS.md

Stack: Laravel 12 / PHP 8.2+ / Blade / TailwindCSS 4 / Vite 7 / SQLite / PHPUnit 11

## Quick start

```bash
composer setup        # install, .env, key:generate, migrate (not fresh), npm install & build
composer dev          # serve + queue:listen + pail (logs) + npm run dev (via concurrently)
composer test         # config:clear && artisan test
php artisan migrate:fresh --seed   # full DB reset
```

## Seed accounts (password: `password`)

| Email              | Role        |
|--------------------|-------------|
| warga@desa.id      | warga       |
| perangkat@desa.id  | perangkat   |
| kades@desa.id      | kades       |
| satpam@desa.id     | warga *     |

> * No `satpam` role exists. Petugas ronda/satpam use `warga` role. Routes under `/warga/ronda/*`.

## Architecture

- **Roles**: `warga`, `perangkat`, `kades`. FK `role_id` on `users`. Checked via `User::hasRole()`. Custom `RoleMiddleware` as alias `role` in `bootstrap/app.php`.
- **Auth**: Custom `AuthController`, session-based (no Breeze/Jetstream).
- **Portals**: `/warga/*`, `/perangkat/*`, `/kades/*`. There is a satpam dashboard view but **no** satpam routes — `SatpamController` is mounted under `/warga/ronda/*`.
- **Status flow**: `baru` → `diverifikasi` → `diproses` → `ditangani` → `selesai` / `ditolak`
- **DB**: SQLite. `.env` sets `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database` (non-default).
- **Timezone**: `Asia/Makassar`
- **Coordinate fallback**: Desa Awa (~ -3.946944, 121.351028), random-offset in forms.

## Migrations

Two files: `0001_01_01_000000_create_users_table.php` (roles + users + sessions tables) and `2026_06_04_000000_create_village_security_tables.php` (all remaining). `DatabaseSeeder.php` seeds everything.

## Testing

- SQLite `:memory:` in `phpunit.xml`. **Roles must be seeded manually** in `setUp()` (no auto-run seeder).
- `SecuritySystemTest` covers full E2E: register → report → verify → assign → handle → patrol log → monitor/rekap.
- Focused: `php artisan test --filter=SecuritySystemTest`

## Dev shortcuts

- `composer dev` → 4 concurrent processes: `serve`, `queue:listen`, `pail`, `npm run dev`
- Role switch (local): `/simulasi/switch/{warga|perangkat|kades}`
- Real-time updates: `GET /reports/realtime-updates?last_id=N`
- Audit log: `ActivityLog::log(string $activity, ?int $userId = null)`

## Uploads

`storage/app/public/{attachments,patrol_attachments}/`. Symlink at `public/storage`. Max 5 MB images.

## Conventions

- `DB::transaction()` for all multi-step report/incident/handling updates.
- Notifications via `Notification::create()` (no Laravel notification system).
- Form validation in controllers. Upload limit 5 MB.
