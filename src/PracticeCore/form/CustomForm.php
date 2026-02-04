<?php

declare(strict_types=1);

namespace PracticeCore\form;

use pocketmine\form\Form;
use pocketmine\player\Player;

final class CustomForm implements Form
{
    private string $title;
    /** @var array<int, array<string, mixed>> */
    private array $content = [];
    /** @var callable */
    private $handler;

    /**
     * @param callable $handler function(Player $player, array|null $data): void
     */
    public function __construct(callable $handler)
    {
        $this->title = "";
        $this->handler = $handler;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function addDropdown(string $text, array $options, int $default = 0): void
    {
        $this->content[] = [
            "type" => "dropdown",
            "text" => $text,
            "options" => $options,
            "default" => $default,
        ];
    }

    public function addInput(string $text, string $placeholder = "", string $default = ""): void
    {
        $this->content[] = [
            "type" => "input",
            "text" => $text,
            "placeholder" => $placeholder,
            "default" => $default,
        ];
    }

    public function jsonSerialize(): array
    {
        return [
            "type" => "custom_form",
            "title" => $this->title,
            "content" => $this->content,
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        ($this->handler)($player, $data);
    }
}
