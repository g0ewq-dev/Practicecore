<?php

declare(strict_types=1);

namespace PracticeCore\command;

use PracticeCore\PracticeCore;
use PracticeCore\form\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class LeaderboardCommand extends Command
{
    private const TYPES = ["Elo", "Kills", "Deaths"];

    public function __construct(private readonly PracticeCore $plugin)
    {
        parent::__construct("leaderboard", "View top 10 stats", "/leaderboard");
        $this->setPermission("practicecore.command.leaderboard");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Use this command in-game.");
            return true;
        }

        $form = new SimpleForm(function (Player $player, ?int $data): void {
            if ($data === null) {
                return;
            }
            if (!isset(self::TYPES[$data])) {
                $player->sendMessage("§cInvalid leaderboard type.");
                return;
            }
            $type = strtolower(self::TYPES[$data]);
            $this->sendLeaderboard($player, $type);
        });

        $form->setTitle("Leaderboards");
        $form->setContent("Choose a leaderboard category.");
        foreach (self::TYPES as $type) {
            $form->addButton($type);
        }
        $sender->sendForm($form);
        return true;
    }

    private function sendLeaderboard(Player $player, string $type): void
    {
        $stats = $this->plugin->getLeaderboardManager()->getAllStats();
        uasort($stats, function (array $a, array $b) use ($type): int {
            return $b[$type] <=> $a[$type];
        });

        $player->sendMessage("§6Top 10 {$type} Leaderboard:");
        $rank = 1;
        foreach ($stats as $name => $data) {
            $player->sendMessage("§e{$rank}. §f{$name} - {$data[$type]}");
            $rank++;
            if ($rank > 10) {
                break;
            }
        }
        if ($rank === 1) {
            $player->sendMessage("§cNo data yet.");
        }
    }
}
