<?php

declare(strict_types=1);

namespace PracticeCore\form;

use pocketmine\form\Form;
use pocketmine\player\Player;

final class ModalForm implements Form
{
    private string $title;
    private string $content;
    private string $button1;
    private string $button2;
    /** @var callable */
    private $handler;

    /**
     * @param callable $handler function(Player $player, bool|null $data): void
     */
    public function __construct(callable $handler)
    {
        $this->title = "";
        $this->content = "";
        $this->button1 = "Yes";
        $this->button2 = "No";
        $this->handler = $handler;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setButtons(string $button1, string $button2): void
    {
        $this->button1 = $button1;
        $this->button2 = $button2;
    }

    public function jsonSerialize(): array
    {
        return [
            "type" => "modal",
            "title" => $this->title,
            "content" => $this->content,
            "button1" => $this->button1,
            "button2" => $this->button2,
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        ($this->handler)($player, $data);
    }
}
