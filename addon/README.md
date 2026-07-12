# Minelog Waypoints — Bedrock add-on

A Minecraft Bedrock behavior pack that records your **exact** position with a label via slash
commands and stores them as JSON in a world dynamic property. Because add-ons on Realms/clients
cannot make HTTP requests, you export the JSON in-game and paste it into the Minelog web app.

The commands are registered through the stable Custom Commands API, so they work on vanilla
Bedrock with **no experiments**. Once cheats are on, any player can run them (they don't need
operator permission).

> ⚠️ **Cheats must be enabled on the world.** Bedrock only exposes custom slash commands when
> cheats are turned on, and **turning cheats on disables achievements** for that world. This is
> a Minecraft limitation, not something the add-on can change.

## Commands

| Command | Description |
| --- | --- |
| `/wp:save "<label>"` | Save your current position with a label (quote multi-word labels) |
| `/wp:list` | List saved waypoints |
| `/wp:remove <n>` | Remove waypoint number `n` (from `/wp:list`) |
| `/wp:export` | Print the full JSON log to copy into Minelog |
| `/wp:clear true` | Delete all saved waypoints |
| `/wp:help` | Show usage |

## Installing

The easiest option is to **download the latest `minelog-<version>.mcpack` from the
[Releases page](https://github.com/erikaheidi/minelog/releases/latest)** and double-click it —
Minecraft imports it automatically (no tools needed, ideal on Windows).

To build it from source instead, zip the **contents** of this `addon/` directory (so
`manifest.json` is at the zip root) and name it `minelog.mcpack`:

```bash
cd addon && zip -r ../minelog.mcpack . -x 'README.md' && cd ..
```

Either way, double-click `minelog.mcpack` to import it into Minecraft, then set up the world:

1. Enable the **behavior pack** on the world (world/Realm settings → Behavior Packs).
2. Turn on **Cheats** (world settings → Game → Cheats) so the `/wp:` commands are available.
   Note this **disables achievements** for that world.

No experiments are required — the pack uses the stable Custom Commands API and needs Minecraft
**1.21.80 or newer** (where `@minecraft/server` 2.0.0 shipped).

## Releasing (maintainers)

Publishing a GitHub release triggers the `release-addon` workflow
([`.github/workflows/release-addon.yml`](../.github/workflows/release-addon.yml)), which packages
this directory into `minelog.mcpack` and attaches it to the release.

The release tag **must** match the `version` in [`manifest.json`](manifest.json) — the workflow
verifies this and fails the build on a mismatch, so you can't ship a release without bumping the
manifest. Tag `v1.0.0` (or `1.0.0`) for manifest version `[1, 0, 0]`.

## Getting data into Minelog

1. Play on the Switch and `/wp:save "<label>"` as you explore.
2. Join the same Realm from a **PC or phone** Bedrock client (with the pack enabled) and run
   `/wp:export`.
3. Copy the printed JSON line and paste it into Minelog's **Import** page.

## Storage / limits

Waypoints live in a single world dynamic property `minelog:waypoints`. String dynamic
properties max out at 32,767 characters (~250–270 waypoints). The add-on warns you as the log
approaches that size — export and import before it fills up.
