<?php

declare(strict_types=1);

namespace Xekvern\Core;

use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\player\GameMode;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Provider\Event\PlayerLoadEvent;
use Xekvern\Core\Utils\Utils;

class NexusEvents implements Listener
{
    
    /** @var Nexus */
    private $core;

    /**
     * NexusListener constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
    }

    /**
     * @priority LOWEST
     * @param PlayerLoadEvent $event
     */
    public function onPlayerLoad(PlayerLoadEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $server = $this->core->getServer();
        $players = count($server->getOnlinePlayers()) - 1;
        $maxPlayers = $this->core->getServer()->getMaxPlayers();
        if ($players >= ($maxPlayers - Nexus::EXTRA_SLOTS)) {
            $player = $event->getPlayer();
            if ((!$player->hasPermission("permission.join.full")) and $players < ($maxPlayers + Nexus::EXTRA_SLOTS)) {
                $ev = new PlayerKickEvent($player, "Server is full", "Server is Full!", "Server is Full!");
                $ev->call();
                return;
            }
        }
        $player->sendMessage(" ");
        $player->sendMessage(Utils::centerAlignText(TextFormat::WHITE . "Welcome " . TextFormat::GOLD . TextFormat::BOLD . $player->getName() . TextFormat::RESET . ", to " . TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "NeXus" . TextFormat::DARK_AQUA . "PE" . TextFormat::RESET . TextFormat::GRAY . " OP Factions", 58));
        $player->sendMessage(Utils::centerAlignText(TextFormat::GRAY . TextFormat::ITALIC . "Season III of Minecraft Factions", 58));
        $player->sendMessage(Utils::centerAlignText(TextFormat::BOLD . TextFormat::RED . "SHOP: " . TextFormat::RESET . TextFormat::WHITE . "store.nexuspe.net", 58));
        $player->sendMessage(Utils::centerAlignText(TextFormat::BOLD . TextFormat::BLUE . "DISCORD: " . TextFormat::RESET . TextFormat::WHITE . "discord.gg/nexuspe", 58));
        $player->sendMessage(Utils::centerAlignText(TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "RECOMMENDED VERSION: " . TextFormat::RESET . TextFormat::WHITE . ProtocolInfo::MINECRAFT_VERSION, 58));
        $player->sendMessage(" ");
        /** @var NexusPlayer $player */
        if ($player->getDataSession()->getRank()->getIdentifier() >= Rank::TRIAL_MODERATOR and $player->getDataSession()->getRank()->getIdentifier() <= Rank::OWNER) {
            return;
        }
        $player->setGamemode(GameMode::SURVIVAL());
    }

    /**
     * @priority NORMAL
     * @param QueryRegenerateEvent $event
     */
    public function onQueryRegenerate(QueryRegenerateEvent $event): void
    {
        $maxPlayers = $this->core->getServer()->getMaxPlayers();
        $maxSlots = $maxPlayers - Nexus::EXTRA_SLOTS;
        if ($maxSlots < 0) {
            $event->getQueryInfo()->setMaxPlayerCount(20);
            return;
        }
        $players = count($this->core->getServer()->getOnlinePlayers());
        if ($players === $maxPlayers) {
            $event->getQueryInfo()->setMaxPlayerCount($maxPlayers);
            return;
        }
        if ($maxSlots <= $players) {
            if ($players === $maxSlots) {
                $event->getQueryInfo()->setMaxPlayerCount($maxSlots + 1);
                return;
            }
            $event->getQueryInfo()->setMaxPlayerCount($maxSlots + $players + 1);
            return;
        }
        $event->getQueryInfo()->setMaxPlayerCount($maxSlots);
    }

    /**
     * @priority LOWEST
     * @param LeavesDecayEvent $event
     */
    public function onLeavesDecay(LeavesDecayEvent $event): void
    {
        $level = $event->getBlock()->getPosition()->getWorld();
        if ($level->getDisplayName() !== Faction::CLAIM_WORLD) {
            $event->cancel();
            return;
        }
    }
}
