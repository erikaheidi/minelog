import { world, system } from "@minecraft/server";

/**
 * Minelog Waypoints
 *
 * Chat commands to record your exact position with a label. Waypoints are stored
 * as a JSON array in a single world dynamic property, written to the world's
 * LevelDB on disk (survives restarts and works on Realms). Export the JSON with
 * `!wp export` and paste it into the Minelog web app.
 *
 * Commands:
 *   !wp save <label>   Save your current position with a label
 *   !wp list           List saved waypoints
 *   !wp remove <n>     Remove waypoint number <n> (see !wp list)
 *   !wp export         Print the full JSON log to copy into Minelog
 *   !wp clear confirm  Delete all saved waypoints
 *   !wp help           Show usage
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
 * @param {import("@minecraft/server").Player} player
 * @param {string} label
 */
function saveCommand(player, label) {
    if (!label) {
        player.sendMessage("§cUsage: !wp save <label>");
        return;
    }

    const loc = player.location;
    const waypoints = loadWaypoints();
    waypoints.push({
        id: newId(),
        label,
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
            "§eWarning: the waypoint log is getting large. Run §f!wp export§e and import into Minelog soon."
        );
    }
}

/** @param {import("@minecraft/server").Player} player */
function listCommand(player) {
    const waypoints = loadWaypoints();
    if (waypoints.length === 0) {
        player.sendMessage("§7No waypoints saved yet. Use §f!wp save <label>§7.");
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
 * @param {string} arg
 */
function removeCommand(player, arg) {
    const index = Number.parseInt(arg, 10) - 1;
    const waypoints = loadWaypoints();
    if (Number.isNaN(index) || index < 0 || index >= waypoints.length) {
        player.sendMessage("§cUsage: !wp remove <number> (see !wp list)");
        return;
    }
    const [removed] = waypoints.splice(index, 1);
    saveWaypoints(waypoints);
    player.sendMessage(`§aRemoved §f"${removed.label}"§a. ${waypoints.length} left.`);
}

/** @param {import("@minecraft/server").Player} player */
function exportCommand(player) {
    const waypoints = loadWaypoints();
    if (waypoints.length === 0) {
        player.sendMessage("§7Nothing to export yet.");
        return;
    }
    player.sendMessage(`§a--- Minelog export (${waypoints.length} waypoint(s)) ---`);
    player.sendMessage(JSON.stringify(waypoints));
    player.sendMessage("§a--- copy the line above into Minelog ---");
}

/**
 * @param {import("@minecraft/server").Player} player
 * @param {string} arg
 */
function clearCommand(player, arg) {
    if (arg !== "confirm") {
        player.sendMessage("§eThis deletes ALL waypoints. Run §f!wp clear confirm§e to proceed.");
        return;
    }
    saveWaypoints([]);
    player.sendMessage("§aAll waypoints cleared.");
}

/** @param {import("@minecraft/server").Player} player */
function helpCommand(player) {
    player.sendMessage("§a§lMinelog commands:");
    player.sendMessage("§f!wp save <label> §7— save your current position");
    player.sendMessage("§f!wp list §7— list saved waypoints");
    player.sendMessage("§f!wp remove <n> §7— remove waypoint number n");
    player.sendMessage("§f!wp export §7— print JSON to copy into Minelog");
    player.sendMessage("§f!wp clear confirm §7— delete all waypoints");
}

/**
 * @param {import("@minecraft/server").Player} player
 * @param {string} message the full "!wp ..." message
 */
function handleCommand(player, message) {
    const body = message.slice(3).trim(); // strip "!wp"
    const spaceIndex = body.indexOf(" ");
    const sub = (spaceIndex === -1 ? body : body.slice(0, spaceIndex)).toLowerCase();
    const arg = spaceIndex === -1 ? "" : body.slice(spaceIndex + 1).trim();

    switch (sub) {
        case "save":
            saveCommand(player, arg);
            break;
        case "list":
            listCommand(player);
            break;
        case "remove":
            removeCommand(player, arg);
            break;
        case "export":
            exportCommand(player);
            break;
        case "clear":
            clearCommand(player, arg);
            break;
        default:
            helpCommand(player);
            break;
    }
}

world.beforeEvents.chatSend.subscribe((event) => {
    const message = event.message.trim();
    if (!message.startsWith("!wp")) {
        return;
    }

    // Suppress the command so it isn't broadcast to everyone as chat.
    event.cancel = true;

    const player = event.sender;
    // Command callbacks run in a read-only "before" context; defer state changes
    // (dynamic property writes) to the next tick with system.run.
    system.run(() => handleCommand(player, message));
});
