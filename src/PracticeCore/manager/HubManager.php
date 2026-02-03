<?php

declare(strict_types=1);

namespace PracticeCore\manager;

use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\world\Position;

final class HubManager
{
    private Config $config;

    public function __construct(Plugin $plugin)
    {
        $this->config = new Config($plugin->getDataFolder() . "hub.yml", Config::YAML, []);
    }

    public function setHub(Position $position): void
    {
        $this->config->set("hub", [
            "world" => $position->getWorld()->getFolderName(),
            "x" => $position->getX(),
            "y" => $position->getY(),
            "z" => $position->getZ(),
            "yaw" => $position->getYaw(),
            "pitch" => $position->getPitch(),
        ]);
        $this->config->save();
    }

    /**
     * @return array{world: string, x: float, y: float, z: float, yaw: float, pitch: float}|null
     */
    public function getHub(): ?array
    {
        $hub = $this->config->get("hub");
        return is_array($hub) ? $hub : null;
    }

    public function save(): void
    {
        $this->config->save();
    }
}
