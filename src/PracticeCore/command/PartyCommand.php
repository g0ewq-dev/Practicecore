<?php

declare(strict_types=1);

namespace PracticeCore\command;

use PracticeCore\PracticeCore;
use PracticeCore\form\CustomForm;
use PracticeCore\form\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class PartyCommand extends Command
{
    public function __construct(private readonly PracticeCore $plugin)
    {
        parent::__construct("party", "Open party menu", "/party");
        $this->setPermission("practicecore.command.party");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Use this command in-game.");
            return true;
        }

        $partyManager = $this->plugin->getPartyManager();
        $party = $partyManager->getParty($sender);

        $form = new SimpleForm(function (Player $player, ?int $data) use ($partyManager, $party): void {
            if ($data === null) {
                return;
            }
            if ($party === null) {
                if ($data === 0) {
                    $partyManager->createParty($player);
                    $player->sendMessage("§aParty created.");
                }
                if ($data === 1) {
                    $this->sendInviteForm($player);
                }
                if ($data === 2) {
                    $invite = $partyManager->getInvite($player);
                    if ($invite === null) {
                        $player->sendMessage("§cNo pending invites.");
                        return;
                    }
                    $leader = $this->plugin->getServer()->getPlayerExact($invite);
                    if (!$leader instanceof Player) {
                        $player->sendMessage("§cInvite expired.");
                        $partyManager->clearInvite($player);
                        return;
                    }
                    $leaderParty = $partyManager->getParty($leader) ?? $partyManager->createParty($leader);
                    $leaderParty->addMember($player);
                    $partyManager->clearInvite($player);
                    $player->sendMessage("§aYou joined {$leader->getName()}'s party.");
                }
                return;
            }

            if ($data === 0) {
                if ($party->isLeader($player)) {
                    $partyManager->disbandParty($party);
                } else {
                    $party->removeMember($player);
                    $player->sendMessage("§cYou left the party.");
                }
            }
            if ($data === 1) {
                if (!$party->isLeader($player)) {
                    $player->sendMessage("§cOnly the party leader can invite.");
                    return;
                }
                $this->sendInviteForm($player);
            }
        });

        $form->setTitle("Party");

        if ($party === null) {
            $form->setContent("You are not in a party.");
            $form->addButton("Create Party");
            $form->addButton("Invite Player");
            $form->addButton("Accept Invite");
        } else {
            $form->setContent("Leader: {$party->getLeader()->getName()}\nMembers: " . count($party->getMembers()));
            $form->addButton($party->isLeader($sender) ? "Disband Party" : "Leave Party");
            $form->addButton("Invite Player");
        }

        $sender->sendForm($form);
        return true;
    }

    private function sendInviteForm(Player $player): void
    {
        $online = array_values(array_filter(
            $this->plugin->getServer()->getOnlinePlayers(),
            fn(Player $onlinePlayer) => $onlinePlayer !== $player
        ));

        if (count($online) === 0) {
            $player->sendMessage("§cNo players to invite.");
            return;
        }

        $form = new CustomForm(function (Player $sender, ?array $data) use ($online): void {
            if ($data === null) {
                return;
            }
            $index = $data[0] ?? null;
            if (!is_int($index) || !isset($online[$index])) {
                $sender->sendMessage("§cInvalid selection.");
                return;
            }
            $target = $online[$index];
            $this->plugin->getPartyManager()->invite($sender, $target);
            $sender->sendMessage("§aInvite sent to {$target->getName()}.");
            $target->sendMessage("§aYou have been invited to {$sender->getName()}'s party. Use /party to accept.");
        });

        $form->setTitle("Invite Player");
        $form->addDropdown("Player", array_map(fn(Player $p) => $p->getName(), $online));
        $player->sendForm($form);
    }
}
