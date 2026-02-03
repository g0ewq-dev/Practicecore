<?php

declare(strict_types=1);

namespace PracticeCore\util;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

final class KitFactory
{
    public static function giveKit(Player $player, string $mode): void
    {
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        switch (strtolower($mode)) {
            case "boxing":
                $player->getInventory()->addItem(VanillaItems::IRON_SWORD());
                $player->getArmorInventory()->setHelmet(VanillaItems::LEATHER_CAP());
                $player->getArmorInventory()->setChestplate(VanillaItems::LEATHER_TUNIC());
                $player->getArmorInventory()->setLeggings(VanillaItems::LEATHER_PANTS());
                $player->getArmorInventory()->setBoots(VanillaItems::LEATHER_BOOTS());
                break;
            case "nodebuff":
                $player->getInventory()->addItem(VanillaItems::DIAMOND_SWORD());
                $player->getArmorInventory()->setHelmet(VanillaItems::DIAMOND_HELMET());
                $player->getArmorInventory()->setChestplate(VanillaItems::DIAMOND_CHESTPLATE());
                $player->getArmorInventory()->setLeggings(VanillaItems::DIAMOND_LEGGINGS());
                $player->getArmorInventory()->setBoots(VanillaItems::DIAMOND_BOOTS());
                $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::SPLASH_POTION, 0, 16));
                break;
            case "sumo":
            case "fist":
                $player->getInventory()->addItem(VanillaItems::AIR());
                break;
            case "bedfight":
                $player->getInventory()->addItem(VanillaItems::WOODEN_SWORD());
                $player->getInventory()->addItem(VanillaItems::WOOL()->setCount(64));
                $player->getArmorInventory()->setHelmet(VanillaItems::LEATHER_CAP());
                $player->getArmorInventory()->setChestplate(VanillaItems::LEATHER_TUNIC());
                $player->getArmorInventory()->setLeggings(VanillaItems::LEATHER_PANTS());
                $player->getArmorInventory()->setBoots(VanillaItems::LEATHER_BOOTS());
                break;
            default:
                $player->getInventory()->addItem(VanillaItems::IRON_SWORD());
                break;
        }
    }
}
