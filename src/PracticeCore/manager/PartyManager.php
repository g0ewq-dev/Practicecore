<?php

declare(strict_types=1);

namespace PracticeCore\manager;

use PracticeCore\PracticeCore;
use PracticeCore\model\Party;
use pocketmine\player\Player;

final class PartyManager
{
    private PracticeCore $plugin;
    /** @var array<string, Party> */
    private array $parties = [];
    /** @var array<string, string> */
    private array $invites = [];

    public function __construct(PracticeCore $plugin)
    {
        $this->plugin = $plugin;
    }

    public function createParty(Player $leader): Party
    {
        $party = new Party($leader);
        $this->parties[strtolower($leader->getName())] = $party;
        return $party;
    }

    public function disbandParty(Party $party): void
    {
        foreach ($party->getMembers() as $member) {
            $member->sendMessage("§cYour party has been disbanded.");
        }
        unset($this->parties[strtolower($party->getLeader()->getName())]);
    }

    public function getParty(Player $player): ?Party
    {
        foreach ($this->parties as $party) {
            if ($party->isMember($player)) {
                return $party;
            }
        }
        return null;
    }

    public function invite(Player $leader, Player $target): void
    {
        $this->invites[strtolower($target->getName())] = strtolower($leader->getName());
    }

    public function getInvite(Player $player): ?string
    {
        return $this->invites[strtolower($player->getName())] ?? null;
    }

    public function clearInvite(Player $player): void
    {
        unset($this->invites[strtolower($player->getName())]);
    }

    public function handlePlayerQuit(Player $player): void
    {
        $party = $this->getParty($player);
        if ($party === null) {
            return;
        }
        if ($party->isLeader($player)) {
            $this->disbandParty($party);
            return;
        }
        $party->removeMember($player);
    }
}
