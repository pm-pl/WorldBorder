<?php

namespace Hydro\WorldBorder;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;
use function array_diff;
use function scandir;

class WorldBorder extends PluginBase implements Listener {

    public function onEnable(): void {
	    @mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /** 
     * @param PlayerMoveEvent $event
     */
    public function onMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $dat = $this->getConfig()->get("border");
        $prefix = $this->getConfig()->get("prefix") . " ";

        if (isset($dat[$world->getDisplayName()])) {
            $v1 = new Vector3($world->getSpawnLocation()->getX(), 0, $world->getSpawnLocation()->getZ());
            $v2 = new Vector3($player->getLocation()->getX(), 0, $player->getLocation()->getZ());

			// if is equal, get entity to fling
            if (($v2->distance($v1) == $dat[$world->getDisplayName()]) && (!$player->hasPermission("bypass.border"))) {
				if ($this->getConfig()->get("send-border-message", true) === true) {
					$player->sendMessage($prefix . $this->getConfig()->get("border-message"));
				}
				if ($this->getConfig()->get("send-border-title", false) === true) {
					$player->sendTitle($prefix . $this->getConfig()->get("border-title"));
				}
				if ($this->getConfig()->get("send-console-log", true) === true) {
					$this->getServer()->getLogger()->info($prefix . $player . " has hit the WorldBorder!");
				}
				if ($this->getConfig()->get("fling", true) === true) {
					$yaw = $player->getLocation()->getYaw();
					if (!$player->hasPermission("border.bypass")) {
						if (0 <= $yaw and $yaw < 22.5) {
							$player->knockBack(0, 0, 2, 4);
						} elseif (22.5 <= $yaw and $yaw < 67.5) {
							$player->knockBack(-1, -1, 2, 4);
						} elseif (67.5 <= $yaw and $yaw < 112.5) {
							$player->knockBack(-1, -1, 2, 4);
						} elseif (112.5 <= $yaw and $yaw < 157.5) {
							$player->knockBack(-1, -1, 2, 4);
						} elseif (157.5 <= $yaw and $yaw < 202.5) {
							$player->knockBack(-1, 0, 2, 4);
						} elseif (202.5 <= $yaw and $yaw < 247.5) {
							$player->knockBack(-1, 1, 2, 4);
						} elseif (247.5 <= $yaw and $yaw < 292.5) {
							$player->knockBack(-1, 1, 2, 4);
						} elseif (292.5 <= $yaw and $yaw < 337.5) {
							$player->knockBack(-1, 1, 2, 4);
						} elseif (337.5 <= $yaw and $yaw < 360.0) {
							$player->knockBack(-1, 0, 2, 4);
						}
					}
					if ($player->hasPermission("bypass.border")) {
						// inverted knockback, flings player through border.
						if (0 <= $yaw and $yaw < 22.5) {
							$player->knockBack(0, 0, 2, 4);
						} elseif (22.5 <= $yaw and $yaw < 67.5) {
							$player->knockBack(0, 1, 2, 4);
						} elseif (67.5 <= $yaw and $yaw < 112.5) {
							$player->knockBack(0, 1, 2, 4);
						} elseif (112.5 <= $yaw and $yaw < 157.5) {
							$player->knockBack(0, 1, 2, 4);
						} elseif (157.5 <= $yaw and $yaw < 202.5) {
							$player->knockBack(0, 0, 2, 4);
						} elseif (202.5 <= $yaw and $yaw < 247.5) {
							$player->knockBack(0, -1, 2, 4);
						} elseif (247.5 <= $yaw and $yaw < 292.5) {
							$player->knockBack(0, -1, 2, 4);
						} elseif (292.5 <= $yaw and $yaw < 337.5) {
							$player->knockBack(0, -1, 2, 4);
						} elseif (337.5 <= $yaw and $yaw < 360.0) {
							$player->knockBack(0, 0, 2, 4);
						}
					}
				}
			}
			if ($v2->distance($v1) > $dat[$world->getDisplayName()]) {
				if (!$player->hasPermission("bypass.border")) {
                    $player->sendMessage($prefix . $this->getConfig()->get("border-message"));
					$event->cancel();
                } elseif ($player->hasPermission("bypass.border")) {
					$player->sendMessage($prefix . $this->getConfig()->get("bypassed-border-message"));
				}
                // if is beyond, get entity to teleport or freeze
                if (!$player->hasPermission("bypass.border")) {
                    if ($this->getConfig()->get("force-spawn", true) === "true") {
						$defaultWorld = Server::getInstance()->getWorldManager()->getDefaultWorld();
						if ($defaultWorld === null) { // if world is not loaded, world will not be found.
							foreach (array_diff(scandir($this->getServer()->getDataPath() . "worlds"),
								[
									"..",
									"."
								]
									 ) as $loadWorldName) {
								if ($this->getServer()->getWorldManager()->loadWorld($loadWorldName)) {
									$loadedWorldNames = implode(", ", $loadWorldName);
									$this->getLogger()->debug("Successfully loaded [" . $loadedWorldNames . "] for WorldBorder by Hydro");
								}
							}
						}
						$safeSpawn = $defaultWorld->getSafeSpawn();
						$player->teleport($safeSpawn);
					}
					if ($this->getConfig()->get("teleport-message", true) === "true") {
						$player->sendMessage($prefix . $this->getConfig()->get("teleport-spawn-message"));
					}
					if ($this->getConfig()->get("teleport-title-message", false) === "true") {
						$player->sendTitle($prefix . $this->getConfig()->get("teleport-spawn-title"));
					}
					if ($this->getConfig()->get("log-to-console", true) === "true") {
						$this->getLogger()->info($prefix . C::RED . $player . " attempted to bypass border, and was teleported to default world spawn.");
					}
					// No need to throw an error exception for true configs on false. If no teleportation occurs, player will stay in border and receive no message.
				}
			}
		}
	}
}
