<?php

declare(strict_types=1);

namespace PracticeCore;

use PracticeCore\command\BotDuelCommand;
use PracticeCore\command\DuelCommand;
use PracticeCore\command\HubCommand;
use PracticeCore\command\LeaderboardCommand;
use PracticeCore\command\PartyCommand;
use PracticeCore\command\QueueCommand;
use PracticeCore\command\SetArenaCommand;
use PracticeCore\command\SetHubCommand;
use PracticeCore\manager\ArenaManager;
use PracticeCore\manager\BotDuelManager;
use PracticeCore\manager\DuelManager;
use PracticeCore\manager\HubManager;
use PracticeCore\manager\LeaderboardManager;
use PracticeCore\manager\PartyManager;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\player\Player;

final class PracticeCore extends PluginBase implements Listener
{
    private ArenaManager $arenaManager;
    private DuelManager $duelManager;
    private PartyManager $partyManager;
    private LeaderboardManager $leaderboardManager;
    private HubManager $hubManager;
    private BotDuelManager $botDuelManager;

    protected function onEnable(): void
    {
        @mkdir($this->getDataFolder());

        $this->arenaManager = new ArenaManager($this);
        $this->leaderboardManager = new LeaderboardManager($this);
        $this->hubManager = new HubManager($this);
        $this->partyManager = new PartyManager($this);
        $this->duelManager = new DuelManager($this, $this->arenaManager, $this->leaderboardManager);
        $this->botDuelManager = new BotDuelManager($this, $this->arenaManager, $this->leaderboardManager);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->getServer()->getCommandMap()->registerAll("practicecore", [
            new DuelCommand($this),
            new PartyCommand($this),
            new BotDuelCommand($this),
            new LeaderboardCommand($this),
            new QueueCommand($this),
            new SetHubCommand($this),
            new HubCommand($this),
            new SetArenaCommand($this),
        ]);
    }

    public function onDisable(): void
    {
        $this->leaderboardManager->save();
        $this->arenaManager->save();
        $this->hubManager->save();
    }

    public function getArenaManager(): ArenaManager
    {
        return $this->arenaManager;
    }

    public function getDuelManager(): DuelManager
    {
        return $this->duelManager;
    }

    public function getPartyManager(): PartyManager
    {
        return $this->partyManager;
    }

    public function getLeaderboardManager(): LeaderboardManager
    {
        return $this->leaderboardManager;
    }

    public function getHubManager(): HubManager
    {
        return $this->hubManager;
    }

    public function getBotDuelManager(): BotDuelManager
    {
        return $this->botDuelManager;
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $this->duelManager->handlePlayerQuit($player);
        $this->partyManager->handlePlayerQuit($player);
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $this->leaderboardManager->recordDeath($player);

        $killer = $player->getLastDamageCause()?->getDamager();
        if ($killer instanceof Player) {
            $this->leaderboardManager->recordKill($killer);
        }

        $this->duelManager->handlePlayerDeath($player, $killer instanceof Player ? $killer : null);
        $this->botDuelManager->handlePlayerDeath($player);
    }

    public function onEntityDeath(EntityDeathEvent $event): void
    {
        $this->botDuelManager->handleBotDeath($event->getEntity());
    }
}
