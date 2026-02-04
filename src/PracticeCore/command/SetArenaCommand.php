<?php

declare(strict_types=1);

namespace PracticeCore\command;

use PracticeCore\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class SetArenaCommand extends Command
{
    public function __construct(private readonly PracticeCore $plugin)
    {
        parent::__construct("setarena", "Set duel arena spawn point", "/setarena <mode> <1|2>");
        $this->setPermission("practicecore.command.setarena");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Use this command in-game.");
            return true;
        }
        if (!$this->testPermission($sender)) {
            return true;
        }

        if (!isset($args[0], $args[1]) || !in_array($args[1], ["1", "2"], true)) {
            $sender->sendMessage("§cUsage: /setarena <mode> <1|2>");
            return true;
        }

        $mode = strtolower($args[0]);
        $slot = (int) $args[1];

        $this->plugin->getArenaManager()->setArena($mode, $slot, $sender->getPosition());
        $sender->sendMessage("§aSet arena spawn {$slot} for {$mode}.");
        return true;
    }
}
