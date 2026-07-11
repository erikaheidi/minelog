# Minelog Waypoints — Bedrock add-on

A Minecraft Bedrock behavior pack that records your **exact** position with a label via chat
commands and stores them as JSON in a world dynamic property. Because add-ons on Realms/clients
cannot make HTTP requests, you export the JSON in-game and paste it into the Minelog web app.

## Commands

| Command | Description |
| --- | --- |
| `!wp save <label>` | Save your current position with a label |
| `!wp list` | List saved waypoints |
| `!wp remove <n>` | Remove waypoint number `n` (from `!wp list`) |
| `!wp export` | Print the full JSON log to copy into Minelog |
| `!wp clear confirm` | Delete all saved waypoints |
| `!wp help` | Show usage |

## Packaging

Zip the **contents** of this `addon/` directory (so `manifest.json` is at the zip root) and
name it `minelog.mcpack`:

```bash
cd addon && zip -r ../minelog.mcpack . -x 'README.md' && cd ..
```

Double-click `minelog.mcpack` to import it into Minecraft, then enable the **behavior pack** on
your Realm's world (Realm settings → world → Behavior Packs). The pack needs the Scripting/
Beta APIs experiment enabled if your version prompts for it.

## Getting data into Minelog

1. Play on the Switch and `!wp save <label>` as you explore.
2. Join the same Realm from a **PC or phone** Bedrock client (with the pack enabled) and run
   `!wp export`.
3. Copy the printed JSON line and paste it into Minelog's **Import** page.

## Storage / limits

Waypoints live in a single world dynamic property `minelog:waypoints`. String dynamic
properties max out at 32,767 characters (~250–270 waypoints). The add-on warns you as the log
approaches that size — export and import before it fills up.
