<?php

declare(strict_types=1);

namespace PracticeCore\form;

use pocketmine\form\Form;
use pocketmine\player\Player;

final class SimpleForm implements Form
{
    private string $title;
    private string $content;
    /** @var array<int, array{string, ?string, ?string}> */
    private array $buttons = [];
    /** @var callable */
    private $handler;

    /**
     * @param callable $handler function(Player $player, int|null $data): void
     */
    public function __construct(callable $handler)
    {
        $this->title = "";
        $this->content = "";
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

    public function addButton(string $text, ?string $imageType = null, ?string $imagePath = null): void
    {
        $this->buttons[] = [$text, $imageType, $imagePath];
    }

    public function jsonSerialize(): array
    {
        $buttons = [];
        foreach ($this->buttons as [$text, $imageType, $imagePath]) {
            $button = ["text" => $text];
            if ($imageType !== null && $imagePath !== null) {
                $button["image"] = ["type" => $imageType, "data" => $imagePath];
            }
            $buttons[] = $button;
        }

        return [
            "type" => "form",
            "title" => $this->title,
            "content" => $this->content,
            "buttons" => $buttons,
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        ($this->handler)($player, $data);
    }
}
