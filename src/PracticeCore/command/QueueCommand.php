<?php

declare(strict_types=1);

namespace PracticeCore\command;

use PracticeCore\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class QueueCommand extends Command
{
    public function __construct(private readonly PracticeCore $plugin)
    {
        parent::__construct("queue", "Join the duel queue", "/queue");
        $this->setPermission("practicecore.command.queue");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Use this command in-game.");
            return true;
        }

        $this->plugin->getDuelManager()->enqueue($sender);
        return true;
    }
}
