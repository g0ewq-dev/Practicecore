<?php

declare(strict_types=1);

namespace PracticeCore\command;

use PracticeCore\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\Position;

final class HubCommand extends Command
{
    public function __construct(private readonly PracticeCore $plugin)
    {
        parent::__construct("hub", "Teleport to hub", "/hub");
        $this->setPermission("practicecore.command.hub");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Use this command in-game.");
            return true;
        }

        $hub = $this->plugin->getHubManager()->getHub();
        if ($hub === null) {
            $sender->sendMessage("§cHub is not set.");
            return true;
        }

        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($hub["world"]);
        if ($world === null) {
            $sender->sendMessage("§cHub world is not loaded.");
            return true;
        }

        $sender->teleport(new Position($hub["x"], $hub["y"], $hub["z"], $world, $hub["yaw"], $hub["pitch"]));
        $sender->sendMessage("§aTeleported to hub.");
        return true;
    }
}
