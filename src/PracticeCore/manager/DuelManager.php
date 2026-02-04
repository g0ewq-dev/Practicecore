<?php

declare(strict_types=1);

namespace PracticeCore\manager;

use PracticeCore\model\Duel;
use PracticeCore\util\KitFactory;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;

final class DuelManager
{
    /** @var array<string, array{requester: string, mode: string}> */
    private array $requests = [];
    /** @var array<string, Duel> */
    private array $activeDuels = [];
    /** @var array<int, Player> */
    private array $queue = [];

    public function __construct(
        private readonly \PracticeCore\PracticeCore $plugin,
        private readonly ArenaManager $arenaManager,
        private readonly LeaderboardManager $leaderboardManager
    ) {
    }

    public function sendRequest(Player $requester, Player $target, string $mode): void
    {
        $this->requests[strtolower($target->getName())] = [
            "requester" => strtolower($requester->getName()),
            "mode" => $mode,
        ];
    }

    public function getRequest(Player $target): ?array
    {
        return $this->requests[strtolower($target->getName())] ?? null;
    }

    public function clearRequest(Player $target): void
    {
        unset($this->requests[strtolower($target->getName())]);
    }

    public function enqueue(Player $player, string $mode = "boxing"): void
    {
        foreach ($this->queue as $queued) {
            if ($queued->getUniqueId()->equals($player->getUniqueId())) {
                $player->sendMessage("§cYou are already in the queue.");
                return;
            }
        }

        $this->queue[] = $player;
        $player->sendMessage("§aYou have joined the queue.");

        if (count($this->queue) >= 2) {
            $player1 = array_shift($this->queue);
            $player2 = array_shift($this->queue);
            if ($player1 instanceof Player && $player2 instanceof Player) {
                $started = $this->startDuel($player1, $player2, $mode);
                if (!$started) {
                    $player1->sendMessage("§cQueue match failed to start.");
                    $player2->sendMessage("§cQueue match failed to start.");
                }
            }
        }
    }

    public function startDuel(Player $player1, Player $player2, string $mode): bool
    {
        $arena = $this->arenaManager->getArena($mode);
        if ($arena === null || !isset($arena["spawns"]["spawn1"], $arena["spawns"]["spawn2"])) {
            $player1->sendMessage("§cNo arena set for {$mode}.");
            $player2->sendMessage("§cNo arena set for {$mode}.");
            return false;
        }

        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($arena["world"]);
        if (!$world instanceof World) {
            $player1->sendMessage("§cArena world is not loaded.");
            $player2->sendMessage("§cArena world is not loaded.");
            return false;
        }

        $spawn1 = $arena["spawns"]["spawn1"];
        $spawn2 = $arena["spawns"]["spawn2"];

        $player1->teleport($this->createPosition($world, $spawn1));
        $player2->teleport($this->createPosition($world, $spawn2));

        KitFactory::giveKit($player1, $mode);
        KitFactory::giveKit($player2, $mode);

        $duel = new Duel($player1, $player2, $mode);
        $this->activeDuels[strtolower($player1->getName())] = $duel;
        $this->activeDuels[strtolower($player2->getName())] = $duel;

        $player1->sendMessage("§aDuel started against {$player2->getName()} in {$mode}.");
        $player2->sendMessage("§aDuel started against {$player1->getName()} in {$mode}.");
        return true;
    }

    public function handlePlayerDeath(Player $player, ?Player $killer): void
    {
        $duel = $this->activeDuels[strtolower($player->getName())] ?? null;
        if ($duel === null) {
            return;
        }

        $opponent = $duel->getPlayer1() === $player ? $duel->getPlayer2() : $duel->getPlayer1();
        $player->sendMessage("§cYou lost the duel.");
        $opponent->sendMessage("§aYou won the duel!");

        $this->leaderboardManager->adjustElo($opponent, 15);
        $this->leaderboardManager->adjustElo($player, -10);

        unset($this->activeDuels[strtolower($duel->getPlayer1()->getName())]);
        unset($this->activeDuels[strtolower($duel->getPlayer2()->getName())]);

        $hub = $this->plugin->getHubManager()->getHub();
        if ($hub !== null) {
            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($hub["world"]);
            if ($world instanceof World) {
                $player->teleport(new Position($hub["x"], $hub["y"], $hub["z"], $world, $hub["yaw"], $hub["pitch"]));
                $opponent->teleport(new Position($hub["x"], $hub["y"], $hub["z"], $world, $hub["yaw"], $hub["pitch"]));
            }
        }
    }

    public function handlePlayerQuit(Player $player): void
    {
        $duel = $this->activeDuels[strtolower($player->getName())] ?? null;
        if ($duel === null) {
            return;
        }
        $opponent = $duel->getPlayer1() === $player ? $duel->getPlayer2() : $duel->getPlayer1();
        $opponent->sendMessage("§aYour opponent left. You win the duel.");
        $this->leaderboardManager->adjustElo($opponent, 15);
        unset($this->activeDuels[strtolower($duel->getPlayer1()->getName())]);
        unset($this->activeDuels[strtolower($duel->getPlayer2()->getName())]);
    }

    private function createPosition(World $world, array $data): Position
    {
        return new Position(
            (float) $data["x"],
            (float) $data["y"],
            (float) $data["z"],
            $world,
            (float) ($data["yaw"] ?? 0.0),
            (float) ($data["pitch"] ?? 0.0)
        );
    }
}
