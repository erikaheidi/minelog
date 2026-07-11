# Minelog

**A travel log for your Minecraft worlds.** Save the exact coordinates of the places worth
remembering while you play, then browse them back as a gallery and an interactive map — and
share any world publicly with a link.

Minelog is an open source Laravel application paired with a small Minecraft Bedrock add-on.
Contributions are welcome.

## How it works

Minecraft Bedrock add-ons running on Realms and regular clients **cannot make HTTP requests**,
so Minelog can't talk to your game directly. Instead it splits the job in two: a Bedrock
behavior pack captures your positions in-game, and this web app stores, organizes, and shares
them. You move data between the two by copy-pasting a single line of JSON.

1. **Save in-game.** With the *Minelog Waypoints* behavior pack enabled on your world, type
   `!wp save <label>` in chat to record your exact position (x, y, z + dimension) under a label.
   The add-on keeps everything in a world dynamic property, so waypoints survive restarts and
   work on Realms.

2. **Export.** Run `!wp export` and the add-on prints your whole log as a single JSON line in
   chat. (Because the Switch and some consoles can't copy chat text, you can join the same Realm
   from a PC or phone client to run the export and copy it.)

3. **Import.** Paste that JSON into a world on Minelog. The importer creates or updates each
   waypoint, deduplicating on re-import so you can paste repeatedly without creating duplicates.

4. **Browse & share.** View your waypoints as a card gallery or an interactive Leaflet map,
   add notes, tags, and screenshots, then flip a world to **public** to share its map and seed
   with anyone.

The add-on lives in [`addon/`](addon/) — see [`addon/README.md`](addon/README.md) for its
commands and packaging details.

## Tech stack

- **PHP 8.3+** / **Laravel 13**
- **Livewire 4** + **Flux UI** for the interface
- **Tailwind CSS 4** (built with Vite)
- **Fortify** for authentication, **Socialite** for Google sign-in
- **SQLite** by default (any Laravel-supported database works)
- **Pest 4** for tests

## Running it locally

### Requirements

- PHP 8.3 or newer with the usual Laravel extensions
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) 20+ and npm

### Setup

```bash
# 1. Clone and enter the project
git clone https://github.com/<your-username>/minelog.git
cd minelog

# 2. Install PHP and JavaScript dependencies, copy .env, generate a key,
#    run migrations, and build assets — all in one step
composer setup
```

`composer setup` runs `composer install`, creates `.env` from `.env.example`,
generates the app key, migrates the SQLite database, and installs and builds the
frontend. If you prefer to do it by hand:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # SQLite is the default DB_CONNECTION
php artisan migrate
npm install
npm run build
```

### Start the dev server

```bash
composer dev
```

This runs the PHP server, queue worker, log tailer, and Vite together. The app is then
available at **http://localhost:8000**.

> Prefer to run pieces separately? Use `php artisan serve` and `npm run dev` in two terminals.

### Google sign-in (optional)

Sign-in with Google is optional. To enable it, create OAuth credentials in the
[Google Cloud console](https://console.cloud.google.com/) and set these in your `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

Without them, you can still register and log in with an email and password.

## The Minecraft add-on

To try the full loop, package and install the behavior pack:

```bash
cd addon && zip -r ../minelog.mcpack . -x 'README.md' && cd ..
```

Double-click `minelog.mcpack` to import it into Minecraft, enable the behavior pack on your
world (with the Scripting/Beta APIs experiment on if prompted), and start saving waypoints.
Full instructions live in [`addon/README.md`](addon/README.md) and on the app's
**How it Works** page.

## Running the tests

```bash
php artisan test
```

Or run the full CI suite (Pint formatting, PHPStan, and the test suite):

```bash
composer test
```

Before committing PHP changes, format them with Pint:

```bash
vendor/bin/pint --dirty
```

## Contributing

Issues and pull requests are welcome. Please make sure `composer test` passes and your code is
formatted with Pint before opening a PR.

## License

Minelog is open-sourced software licensed under the [MIT license](LICENSE).
