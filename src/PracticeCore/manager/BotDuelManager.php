<?php

declare(strict_types=1);

namespace PracticeCore\manager;

use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\Zombie;
use PracticeCore\util\KitFactory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

final class BotDuelManager
{
    /** @var array<string, int> */
    private array $activeBots = [];

    public function __construct(
        private readonly \PracticeCore\PracticeCore $plugin,
        private readonly ArenaManager $arenaManager,
        private readonly LeaderboardManager $leaderboardManager
    ) {
    }

    public function startBotDuel(Player $player, string $mode, string $difficulty): bool
    {
        $arena = $this->arenaManager->getArena($mode);
        if ($arena === null || !isset($arena["spawns"]["spawn1"], $arena["spawns"]["spawn2"])) {
            $player->sendMessage("§cNo arena set for {$mode}.");
            return false;
        }

        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($arena["world"]);
        if (!$world instanceof World) {
            $player->sendMessage("§cArena world is not loaded.");
            return false;
        }

        $spawn1 = $arena["spawns"]["spawn1"];
        $spawn2 = $arena["spawns"]["spawn2"];

        $player->teleport(new Location(
            (float) $spawn1["x"],
            (float) $spawn1["y"],
            (float) $spawn1["z"],
            $world,
            (float) ($spawn1["yaw"] ?? 0.0),
            (float) ($spawn1["pitch"] ?? 0.0)
        ));

        KitFactory::giveKit($player, $mode);

        $bot = new Zombie(new Location(
            (float) $spawn2["x"],
            (float) $spawn2["y"],
            (float) $spawn2["z"],
            $world,
            (float) ($spawn2["yaw"] ?? 0.0),
            (float) ($spawn2["pitch"] ?? 0.0)
        ));

        $bot->setNameTag(TextFormat::GREEN . "Practice Bot");
        $bot->setNameTagAlwaysVisible(true);
        $bot->setMaxHealth($this->getBotHealth($difficulty));
        $bot->setHealth($bot->getMaxHealth());
        $bot->getNamedTag()->setString("practice_bot_owner", $player->getName());
        $bot->spawnToAll();

        $this->activeBots[strtolower($player->getName())] = $bot->getId();
        $player->sendMessage("§aBot duel started ({$difficulty}).");
        return true;
    }

    public function handleBotDeath(Entity $entity): void
    {
        if (!$entity instanceof Zombie) {
            return;
        }
        $owner = $entity->getNamedTag()->getString("practice_bot_owner", "");
        $ownerKey = strtolower($owner);
        if ($owner === "" || !isset($this->activeBots[$ownerKey])) {
            return;
        }
        unset($this->activeBots[$ownerKey]);
        $player = $this->plugin->getServer()->getPlayerByPrefix($owner);
        if ($player instanceof Player) {
            $player->sendMessage("§aYou defeated the practice bot!");
            $this->leaderboardManager->adjustElo($player, 5);
        }
    }

    public function handlePlayerDeath(Player $player): void
    {
        $name = strtolower($player->getName());
        if (!isset($this->activeBots[$name])) {
            return;
        }
        unset($this->activeBots[$name]);
        $player->sendMessage("§cYou lost to the practice bot.");
        $this->leaderboardManager->adjustElo($player, -5);
    }

    private function getBotHealth(string $difficulty): int
    {
        return match (strtolower($difficulty)) {
            "easy" => 10,
            "hard" => 30,
            default => 20,
        };
    }
}
