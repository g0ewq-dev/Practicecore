<?php

declare(strict_types=1);

namespace PracticeCore\model;

use pocketmine\player\Player;

final class Party
{
    private Player $leader;
    /** @var array<string, Player> */
    private array $members = [];

    public function __construct(Player $leader)
    {
        $this->leader = $leader;
        $this->members[strtolower($leader->getName())] = $leader;
    }

    public function getLeader(): Player
    {
        return $this->leader;
    }

    /**
     * @return Player[]
     */
    public function getMembers(): array
    {
        return array_values($this->members);
    }

    public function addMember(Player $player): void
    {
        $this->members[strtolower($player->getName())] = $player;
    }

    public function removeMember(Player $player): void
    {
        unset($this->members[strtolower($player->getName())]);
    }

    public function isMember(Player $player): bool
    {
        return isset($this->members[strtolower($player->getName())]);
    }

    public function isLeader(Player $player): bool
    {
        return strtolower($this->leader->getName()) === strtolower($player->getName());
    }

    public function setLeader(Player $player): void
    {
        $this->leader = $player;
    }
}
