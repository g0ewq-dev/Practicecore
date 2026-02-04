<?php

declare(strict_types=1);

namespace PracticeCore\command;

use PracticeCore\PracticeCore;
use PracticeCore\form\CustomForm;
use PracticeCore\form\ModalForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class DuelCommand extends Command
{
    private const MODES = ["Boxing", "Sumo", "NoDebuff", "Fist", "BedFight"];

    public function __construct(private readonly PracticeCore $plugin)
    {
        parent::__construct("duel", "Open duel UI or duel a player", "/duel [player]");
        $this->setPermission("practicecore.command.duel");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Use this command in-game.");
            return true;
        }

        if (isset($args[0]) && strtolower($args[0]) === "accept") {
            $request = $this->plugin->getDuelManager()->getRequest($sender);
            if ($request === null) {
                $sender->sendMessage("§cYou have no duel requests.");
                return true;
            }
            $requester = $this->plugin->getServer()->getPlayerExact($request["requester"]);
            if (!$requester instanceof Player) {
                $sender->sendMessage("§cThe requester is no longer online.");
                $this->plugin->getDuelManager()->clearRequest($sender);
                return true;
            }
            $this->plugin->getDuelManager()->clearRequest($sender);
            $this->plugin->getDuelManager()->startDuel($requester, $sender, $request["mode"]);
            return true;
        }

        if (isset($args[0])) {
            $target = $this->plugin->getServer()->getPlayerByPrefix($args[0]);
            if (!$target instanceof Player || $target === $sender) {
                $sender->sendMessage("§cPlayer not found.");
                return true;
            }
            $mode = strtolower(self::MODES[0]);
            $this->plugin->getDuelManager()->sendRequest($sender, $target, $mode);
            $sender->sendMessage("§aSent duel request to {$target->getName()}.");
            $target->sendMessage("§e{$sender->getName()} has challenged you. Use /duel accept to fight.");
            return true;
        }

        $players = array_values(array_filter(
            $this->plugin->getServer()->getOnlinePlayers(),
            fn(Player $player) => $player !== $sender
        ));

        if (count($players) === 0) {
            $sender->sendMessage("§cNo players available to duel.");
            return true;
        }

        $form = new CustomForm(function (Player $player, ?array $data) use ($players): void {
            if ($data === null) {
                return;
            }
            $playerIndex = $data[0] ?? null;
            $modeIndex = $data[1] ?? null;
            if (!is_int($playerIndex) || !isset($players[$playerIndex])) {
                $player->sendMessage("§cInvalid player selection.");
                return;
            }
            if (!is_int($modeIndex) || !isset(self::MODES[$modeIndex])) {
                $player->sendMessage("§cInvalid mode selection.");
                return;
            }
            $target = $players[$playerIndex];
            $mode = strtolower(self::MODES[$modeIndex]);
            $this->plugin->getDuelManager()->sendRequest($player, $target, $mode);
            $player->sendMessage("§aSent duel request to {$target->getName()}.");
            $this->sendAcceptForm($target, $player, $mode);
        });

        $form->setTitle("Duel");
        $form->addDropdown("Select player", array_map(fn(Player $p) => $p->getName(), $players));
        $form->addDropdown("Select mode", self::MODES);
        $sender->sendForm($form);
        return true;
    }

    private function sendModeForm(Player $sender, Player $target): void
    {
        $form = new CustomForm(function (Player $player, ?array $data) use ($target): void {
            if ($data === null) {
                return;
            }
            $modeIndex = $data[0] ?? null;
            if (!is_int($modeIndex) || !isset(self::MODES[$modeIndex])) {
                $player->sendMessage("§cInvalid mode selection.");
                return;
            }
            $mode = strtolower(self::MODES[$modeIndex]);
            $this->plugin->getDuelManager()->sendRequest($player, $target, $mode);
            $player->sendMessage("§aSent duel request to {$target->getName()}.");
            $this->sendAcceptForm($target, $player, $mode);
        });

        $form->setTitle("Select Duel Mode");
        $form->addDropdown("Mode", self::MODES);
        $sender->sendForm($form);
    }

    private function sendAcceptForm(Player $target, Player $requester, string $mode): void
    {
        $form = new ModalForm(function (Player $player, ?bool $data) use ($requester, $mode): void {
            if ($data !== true) {
                $player->sendMessage("§cDuel request declined.");
                $requester->sendMessage("§cYour duel request was declined.");
                return;
            }
            $this->plugin->getDuelManager()->startDuel($requester, $player, $mode);
        });

        $form->setTitle("Duel Request");
        $form->setContent("{$requester->getName()} wants to duel you in {$mode}. Accept?");
        $form->setButtons("Accept", "Decline");
        $target->sendForm($form);
    }
}
