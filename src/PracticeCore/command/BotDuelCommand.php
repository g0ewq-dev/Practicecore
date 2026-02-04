<?php

declare(strict_types=1);

namespace PracticeCore\command;

use PracticeCore\PracticeCore;
use PracticeCore\form\CustomForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class BotDuelCommand extends Command
{
    private const MODES = ["Boxing", "Sumo", "NoDebuff", "Fist", "BedFight"];
    private const DIFFICULTIES = ["Easy", "Normal", "Hard"];

    public function __construct(private readonly PracticeCore $plugin)
    {
        parent::__construct("botduel", "Open bot duel menu", "/botduel");
        $this->setPermission("practicecore.command.botduel");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Use this command in-game.");
            return false;
        }

        $form = new CustomForm(function (Player $player, ?array $data): void {
            if ($data === null) {
                return;
            }
            $modeIndex = $data[0] ?? null;
            $difficultyIndex = $data[1] ?? null;
            if (!is_int($modeIndex) || !isset(self::MODES[$modeIndex])) {
                $player->sendMessage("§cInvalid mode selection.");
                return;
            }
            if (!is_int($difficultyIndex) || !isset(self::DIFFICULTIES[$difficultyIndex])) {
                $player->sendMessage("§cInvalid difficulty selection.");
                return;
            }
            $mode = strtolower(self::MODES[$modeIndex]);
            $difficulty = strtolower(self::DIFFICULTIES[$difficultyIndex]);
            $this->plugin->getBotDuelManager()->startBotDuel($player, $mode, $difficulty);
        });

        $form->setTitle("Bot Duel");
        $form->addDropdown("Mode", self::MODES);
        $form->addDropdown("Difficulty", self::DIFFICULTIES);
        $sender->sendForm($form);
        return true;
    }
}
