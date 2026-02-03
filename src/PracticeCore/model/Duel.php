<?php

declare(strict_types=1);

namespace PracticeCore\model;

use pocketmine\player\Player;

final class Duel
{
    private Player $player1;
    private Player $player2;
    private string $mode;

    public function __construct(Player $player1, Player $player2, string $mode)
    {
        $this->player1 = $player1;
        $this->player2 = $player2;
        $this->mode = $mode;
    }

    public function getPlayer1(): Player
    {
        return $this->player1;
    }

    public function getPlayer2(): Player
    {
        return $this->player2;
    }

    public function getMode(): string
    {
        return $this->mode;
    }
}
