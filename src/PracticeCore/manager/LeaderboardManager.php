<?php

declare(strict_types=1);

namespace PracticeCore\manager;

use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\player\Player;

final class LeaderboardManager
{
    private Config $config;

    public function __construct(Plugin $plugin)
    {
        $this->config = new Config($plugin->getDataFolder() . "data.yml", Config::YAML, []);
    }

    public function recordKill(Player $player): void
    {
        $stats = $this->getStats($player);
        $stats["kills"]++;
        $this->setStats($player, $stats);
    }

    public function recordDeath(Player $player): void
    {
        $stats = $this->getStats($player);
        $stats["deaths"]++;
        $this->setStats($player, $stats);
    }

    public function adjustElo(Player $player, int $delta): void
    {
        $stats = $this->getStats($player);
        $stats["elo"] = max(0, $stats["elo"] + $delta);
        $this->setStats($player, $stats);
    }

    /**
     * @return array{elo: int, kills: int, deaths: int}
     */
    public function getStats(Player $player): array
    {
        $name = strtolower($player->getName());
        $stats = $this->config->get($name, [
            "elo" => 1000,
            "kills" => 0,
            "deaths" => 0,
        ]);

        return [
            "elo" => (int) ($stats["elo"] ?? 1000),
            "kills" => (int) ($stats["kills"] ?? 0),
            "deaths" => (int) ($stats["deaths"] ?? 0),
        ];
    }

    /**
     * @return array<string, array{elo: int, kills: int, deaths: int}>
     */
    public function getAllStats(): array
    {
        $data = $this->config->getAll();
        $results = [];
        foreach ($data as $name => $stats) {
            if (!is_array($stats)) {
                continue;
            }
            $results[$name] = [
                "elo" => (int) ($stats["elo"] ?? 1000),
                "kills" => (int) ($stats["kills"] ?? 0),
                "deaths" => (int) ($stats["deaths"] ?? 0),
            ];
        }
        return $results;
    }

    private function setStats(Player $player, array $stats): void
    {
        $this->config->set(strtolower($player->getName()), $stats);
        $this->config->save();
    }

    public function save(): void
    {
        $this->config->save();
    }
}
