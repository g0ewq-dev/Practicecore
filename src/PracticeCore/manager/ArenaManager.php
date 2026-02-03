<?php

declare(strict_types=1);

namespace PracticeCore\manager;

use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\world\Position;

final class ArenaManager
{
    private Config $config;

    public function __construct(Plugin $plugin)
    {
        $this->config = new Config($plugin->getDataFolder() . "arenas.yml", Config::YAML, [
            "arenas" => [],
        ]);
    }

    public function setArena(string $mode, int $slot, Position $position): void
    {
        $data = $this->config->get("arenas", []);
        $data[$mode]["world"] = $position->getWorld()->getFolderName();
        $data[$mode]["spawns"]["spawn{$slot}"] = [
            "x" => $position->getX(),
            "y" => $position->getY(),
            "z" => $position->getZ(),
            "yaw" => $position->getYaw(),
            "pitch" => $position->getPitch(),
        ];
        $this->config->set("arenas", $data);
        $this->config->save();
    }

    /**
     * @return array{world: string, spawns: array{spawn1?: array<string, float>, spawn2?: array<string, float>}}|null
     */
    public function getArena(string $mode): ?array
    {
        $data = $this->config->get("arenas", []);
        return $data[$mode] ?? null;
    }

    public function save(): void
    {
        $this->config->save();
    }
}
