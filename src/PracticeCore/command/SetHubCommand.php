<?php

declare(strict_types=1);

namespace PracticeCore\command;

use PracticeCore\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class SetHubCommand extends Command
{
    public function __construct(private readonly PracticeCore $plugin)
    {
        parent::__construct("sethub", "Set hub location", "/sethub");
        $this->setPermission("practicecore.command.sethub");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Use this command in-game.");
            return false;
        }
        if (!$this->testPermission($sender)) {
            return false;
        }

        $this->plugin->getHubManager()->setHub($sender->getPosition());
        $sender->sendMessage("§aHub set.");
        return true;
    }
}
