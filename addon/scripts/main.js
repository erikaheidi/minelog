import {
    world,
    system,
    CommandPermissionLevel,
    CustomCommandParamType,
    CustomCommandStatus,
} from "@minecraft/server";
import { ModalFormData, FormCancelationReason } from "@minecraft/server-ui";

/**
 * Minelog Waypoints
 *
 * Slash commands to record your exact position with a label. Waypoints are
 * stored as a JSON array in a single world dynamic property, written to the
 * world's LevelDB on disk (survives restarts and works on Realms). Export the
 * JSON with `/wp:export` and paste it into the Minelog web app.
 *
 * These are registered through the stable Custom Commands API
 * (`system.beforeEvents.startup`), which works on vanilla Bedrock with no
 * experiments. `permissionLevel: Any` + `cheatsRequired: false` lets every
 * player run them, not just operators.
 *
 * Commands:
 *   /wp:save "<label>"  Save your current position with a label
 *   /wp:list            List saved waypoints
 *   /wp:remove <n>      Remove waypoint number <n> (see /wp:list)
 *   /wp:export          Print the full JSON log to copy into Minelog
 *   /wp:clear <true>    Delete all saved waypoints
 *   /wp:help            Show usage
 */

const STORAGE_KEY = "minelog:waypoints";

// A string dynamic property maxes out at 32767 chars. Warn well before that so
// there's room to export before data would be lost on the next save.
const SIZE_WARN_THRESHOLD = 30000;

const DIMENSION_MAP = {
    "minecraft:overworld": "overworld",
    "minecraft:nether": "nether",
    "minecraft:the_end": "end",
};

/**
 * @typedef {Object} Waypoint
 * @property {string} id
 * @property {string} label
 * @property {number} x
 * @property {number} y
 * @property {number} z
 * @property {string} dimension
 * @property {string} capturedAt
 * @property {string} player
 */

/** @returns {Waypoint[]} */
function loadWaypoints() {
    const raw = world.getDynamicProperty(STORAGE_KEY);
    if (typeof raw !== "string" || raw.length === 0) {
        return [];
    }
    try {
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

/**
 * @param {Waypoint[]} waypoints
 * @returns {number} the serialized length, so callers can warn near the limit
 */
function saveWaypoints(waypoints) {
    const serialized = JSON.stringify(waypoints);
    world.setDynamicProperty(STORAGE_KEY, serialized);
    return serialized.length;
}

function newId() {
    return `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 10)}`;
}

function mapDimension(dimensionId) {
    return DIMENSION_MAP[dimensionId] ?? "overworld";
}

/**
 * Command callbacks run in a read-only context, so we can't write dynamic
 * properties or send messages from inside them. Pull the player off the origin
 * and hand the actual work to this helper, which is invoked on the next tick
 * via `system.run`.
 *
 * @param {import("@minecraft/server").CustomCommandOrigin} origin
 * @returns {import("@minecraft/server").Player | undefined}
 */
function playerFromOrigin(origin) {
    const entity = origin.sourceEntity;
    if (entity && entity.typeId === "minecraft:player") {
        return /** @type {import("@minecraft/server").Player} */ (entity);
    }
    return undefined;
}

/**
 * @param {import("@minecraft/server").Player} player
 * @param {string} label
 */
function saveCommand(player, label) {
    const trimmed = (label ?? "").trim();
    if (!trimmed) {
        player.sendMessage('§cUsage: /wp:save "<label>"');
        return;
    }

    const loc = player.location;
    const waypoints = loadWaypoints();
    waypoints.push({
        id: newId(),
        label: trimmed,
        x: Math.round(loc.x),
        y: Math.round(loc.y),
        z: Math.round(loc.z),
        dimension: mapDimension(player.dimension.id),
        capturedAt: new Date().toISOString(),
        player: player.name,
    });

    const size = saveWaypoints(waypoints);
    const last = waypoints[waypoints.length - 1];
    player.sendMessage(
        `§aSaved §f"${last.label}" §aat §f${last.x}, ${last.y}, ${last.z} §7(${last.dimension}) — ${waypoints.length} total`
    );

    if (size > SIZE_WARN_THRESHOLD) {
        player.sendMessage(
            "§eWarning: the waypoint log is getting large. Run §f/wp:export§e and import into Minelog soon."
        );
    }
}

/** @param {import("@minecraft/server").Player} player */
function listCommand(player) {
    const waypoints = loadWaypoints();
    if (waypoints.length === 0) {
        player.sendMessage('§7No waypoints saved yet. Use §f/wp:save "<label>"§7.');
        return;
    }
    player.sendMessage(`§a${waypoints.length} waypoint(s):`);
    waypoints.forEach((wp, i) => {
        player.sendMessage(
            `§7${i + 1}. §f${wp.label} §7— ${wp.x}, ${wp.y}, ${wp.z} (${wp.dimension})`
        );
    });
}

/**
 * @param {import("@minecraft/server").Player} player
 * @param {number} number the 1-based waypoint number from /wp:list
 */
function removeCommand(player, number) {
    const index = number - 1;
    const waypoints = loadWaypoints();
    if (!Number.isInteger(index) || index < 0 || index >= waypoints.length) {
        player.sendMessage("§cUsage: /wp:remove <number> (see /wp:list)");
        return;
    }
    const [removed] = waypoints.splice(index, 1);
    saveWaypoints(waypoints);
    player.sendMessage(`§aRemoved §f"${removed.label}"§a. ${waypoints.length} left.`);
}

/**
 * Show the export JSON in a form text field. Unlike chat, a form text field on
 * a PC/Windows client is a real input the player can select-all (Ctrl+A) and
 * copy (Ctrl+C) — chat text isn't copyable on most platforms, which is why the
 * plain chat print alone doesn't work.
 *
 * A form can't open while the player is still in the command/chat UI: `show`
 * resolves with `cancelationReason: UserBusy`. Retry on the next tick until the
 * UI is free (bounded so we never loop forever if the player keeps it open).
 *
 * @param {import("@minecraft/server").Player} player
 * @param {string} json
 * @param {number} count
 * @param {number} [attempt]
 */
function showExportForm(player, json, count, attempt = 0) {
    const form = new ModalFormData().title("Minelog export").textField(
        `${count} waypoint(s). Tap the field, select all (Ctrl+A) and copy (Ctrl+C), then paste into Minelog:`,
        "waypoint JSON",
        { defaultValue: json }
    );

    form.show(player)
        .then((response) => {
            if (
                response.canceled &&
                response.cancelationReason === FormCancelationReason.UserBusy &&
                attempt < 40
            ) {
                system.run(() => showExportForm(player, json, count, attempt + 1));
            }
        })
        .catch(() => {
            // If forms aren't available for any reason, the chat fallback below
            // still gives the player the JSON.
        });
}

/** @param {import("@minecraft/server").Player} player */
function exportCommand(player) {
    const waypoints = loadWaypoints();
    if (waypoints.length === 0) {
        player.sendMessage("§7Nothing to export yet.");
        return;
    }

    const json = JSON.stringify(waypoints);

    // Primary path: a copyable form field (works on PC clients).
    showExportForm(player, json, waypoints.length);

    // Fallback for platforms without form copy: still print to chat.
    player.sendMessage(`§a--- Minelog export (${waypoints.length} waypoint(s)) ---`);
    player.sendMessage(json);
    player.sendMessage("§a--- or copy from the export box that just opened ---");
}

/**
 * @param {import("@minecraft/server").Player} player
 * @param {boolean | undefined} confirm
 */
function clearCommand(player, confirm) {
    if (confirm !== true) {
        player.sendMessage("§eThis deletes ALL waypoints. Run §f/wp:clear true§e to proceed.");
        return;
    }
    saveWaypoints([]);
    player.sendMessage("§aAll waypoints cleared.");
}

/** @param {import("@minecraft/server").Player} player */
function helpCommand(player) {
    player.sendMessage("§a§lMinelog commands:");
    player.sendMessage('§f/wp:save "<label>" §7— save your current position');
    player.sendMessage("§f/wp:list §7— list saved waypoints");
    player.sendMessage("§f/wp:remove <n> §7— remove waypoint number n");
    player.sendMessage("§f/wp:export §7— print JSON to copy into Minelog");
    player.sendMessage("§f/wp:clear true §7— delete all waypoints");
}

/**
 * Wrap a per-player command body so it resolves the player from the origin,
 * defers the work to the next tick, and reports a result to the command line.
 *
 * @param {(player: import("@minecraft/server").Player, ...args: any[]) => void} handler
 * @returns {(origin: import("@minecraft/server").CustomCommandOrigin, ...args: any[]) => import("@minecraft/server").CustomCommandResult}
 */
function playerCommand(handler) {
    return (origin, ...args) => {
        const player = playerFromOrigin(origin);
        if (!player) {
            return {
                status: CustomCommandStatus.Failure,
                message: "This command must be run by a player.",
            };
        }
        system.run(() => handler(player, ...args));
        return { status: CustomCommandStatus.Success };
    };
}

system.beforeEvents.startup.subscribe(({ customCommandRegistry }) => {
    const base = {
        permissionLevel: CommandPermissionLevel.Any,
        cheatsRequired: false,
    };

    customCommandRegistry.registerCommand(
        {
            ...base,
            name: "wp:save",
            description: "Save your current position with a label",
            mandatoryParameters: [{ name: "wp:label", type: CustomCommandParamType.String }],
        },
        playerCommand(saveCommand)
    );

    customCommandRegistry.registerCommand(
        {
            ...base,
            name: "wp:list",
            description: "List saved waypoints",
        },
        playerCommand(listCommand)
    );

    customCommandRegistry.registerCommand(
        {
            ...base,
            name: "wp:remove",
            description: "Remove a waypoint by its number (see /wp:list)",
            mandatoryParameters: [{ name: "wp:number", type: CustomCommandParamType.Integer }],
        },
        playerCommand(removeCommand)
    );

    customCommandRegistry.registerCommand(
        {
            ...base,
            name: "wp:export",
            description: "Print the full JSON log to copy into Minelog",
        },
        playerCommand(exportCommand)
    );

    customCommandRegistry.registerCommand(
        {
            ...base,
            name: "wp:clear",
            description: "Delete all saved waypoints",
            optionalParameters: [{ name: "wp:confirm", type: CustomCommandParamType.Boolean }],
        },
        playerCommand(clearCommand)
    );

    customCommandRegistry.registerCommand(
        {
            ...base,
            name: "wp:help",
            description: "Show Minelog command usage",
        },
        playerCommand(helpCommand)
    );
});
