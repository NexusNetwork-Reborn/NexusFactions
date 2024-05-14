<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Watchdog;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Provider\Event\PlayerLoadEvent;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use Xekvern\Core\Server\Watchdog\Task\CommandLogTask;
use Xekvern\Core\Server\Watchdog\Task\PlayerCommandLogTask;
use muqsit\invmenu\InvMenu;
use pocketmine\block\tile\Container;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Utils\Utils;

class WatchdogEvents implements Listener {

    /** @var Nexus */
    private $core;

    /** @var string[] */
    private $keys = [
        "NTMzNDpwdm91VkpJTkJ1Mk1BN0ZTdDR0aG1KMGxvQVI3NVFSTg==",
        "NTMzNTpCcERObUd0MG5qMGxVYmpFbm5xck41dU9NelNyUmJadw==",
        "NTMzNjpza2pUWUV1MlIyNzUzdFE1Q3lyUGE1SjVud1BZUndZVA==",
        "NTMzNzpEdGVlaW82RDJNeWJMYVdnWktENlFMeEVSQUlwWm84Sw==",
        "NTMzODpaaTJEclByM09pbllLbDU2UmlDWmtRZEU4dlBjYjFxeg=="
    ];

    /** @var int */
    private $count = 0;

    /**
     * WatchdogEvents constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event) : void{
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if($player->hasNoClientPredictions() and $player->isFrozen()) {
            $name = $player->getName();
            $reason = "Leaving while being frozen";
            $time = 604800;
            $this->core->getServer()->dispatchCommand(new ConsoleCommandSender(Nexus::getInstance()->getServer(), Nexus::getInstance()->getServer()->getLanguage()), "tempban $name $time $reason");
        }
    }

    /**
     * @priority LOW
     * @param PlayerPreLoginEvent $event
     *
     * @throws TranslatonException
     */
    public function onPlayerPreLogin(PlayerPreLoginEvent $event): void {
        if($this->core->getServerManager()->getWatchdogHandler()->isBanned($event->getPlayerInfo()->getUsername())) {
            $ban = $this->core->getServerManager()->getWatchdogHandler()->getBan($event->getPlayerInfo()->getUsername());
            if($ban->getExpiration() === 0) {
                $timeString = "Forever";
            }
            else {
                $expiration = ($ban->getTime() + $ban->getExpiration()) - time();
                $timeString = Utils::secondsToTime($expiration);
            }
            $message = Translation::getMessage("banMessage", [
                "name" => TextFormat::RED . $ban->getEffector(),
                "reason" => TextFormat::YELLOW . $ban->getReason(),
                "time" => TextFormat::RED . $timeString
            ]);
            $event->setKickFlag(1, $message);
            return;
        }
        if(time() - $this->core->getStartTime() <= 5) {
            $event->setKickFLag(1, TextFormat::RED . "The server is restarting!");
            return;
        }
    }

    /**
     * @priority LOWEST
     * @param PlayerLoadEvent $event
     */
    public function onPlayerLoad(PlayerLoadEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $ipAddress = $player->getNetworkSession()->getIp();
        $uuid = $player->getUniqueId()->toString();
        $stmt = $this->core->getMySQLProvider()->getDatabase()->prepare("SELECT riskLevel FROM ipAddress WHERE ipAddress = ? AND uuid = ?");
        $stmt->bind_param("ss", $ipAddress, $uuid);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->close();
        if($result === null) {
            ++$this->count;
            if($this->count > count($this->keys) - 1) {
                $this->count = 0;
            }
            $key = $this->keys[$this->count++];
            //$this->core->getServer()->getAsyncPool()->submitTaskToWorker(new ProxyCheckTask($player->getName(), $ipAddress, $key), 0);
            return;
        }
        if($result === 1) {
            $player->close(null, TextFormat::RED . "A malicious ip swapper was detected!");
            return;
        }
        if($this->core->getServerManager()->getWatchdogHandler()->isBanned($player->getName())) {
            $ban = $this->core->getServerManager()->getWatchdogHandler()->getBan($player->getName());
            if($ban->getExpiration() === 0) {
                $timeString = "Forever";
            }
            else {
                $expiration = ($ban->getTime() + $ban->getExpiration()) - time();
                $timeString = Utils::secondsToTime($expiration);
            }
            $message = Translation::getMessage("banMessage", [
                "name" => TextFormat::RED . $ban->getEffector(),
                "reason" => TextFormat::YELLOW . $ban->getReason(),
                "time" => TextFormat::RED . $timeString
            ]);
            $player->kick($message);
            return;
        }
    }

    /**
     * @priority LOW
     * @param PlayerChatEvent $event
     *
     * @throws TranslatonException
     */
    public function onPlayerChat(PlayerChatEvent $event): void {
        /** @var NexusPlayer $player */
        $player = $event->getPlayer();
        if(!$player->isLoaded()) {
            return;
        }
        $rank = $player->getDataSession()->getRank();
        if($this->core->isGlobalMuted() and ($rank->getIdentifier() < Rank::TRIAL_MODERATOR or $rank->getIdentifier() > Rank::OWNER)) {
            $player->sendMessage(TextFormat::RED . "Chat is currently staff only!");
            $event->cancel();
            return;
        }
        if($this->core->getServerManager()->getWatchdogHandler()->isMuted($player->getName())) {
            $ban = $this->core->getServerManager()->getWatchdogHandler()->getMute($player->getName());
            $expiration = ($ban->getTime() + $ban->getExpiration()) - time();
            $timeString = Utils::secondsToTime($expiration);
            $message = Translation::getMessage("muteMessage", [
                "name" => TextFormat::RED . $ban->getEffector(),
                "reason" => TextFormat::YELLOW . $ban->getReason(),
                "time" => TextFormat::RED . $timeString
            ]);
            $player->sendMessage($message);
            $event->cancel();
        }
    }

    /**
     * @priority LOWEST
     * @param CommandEvent $event
     *
     * @throws TranslatonException
     */
    public function onPlayerCommandPreprocess(CommandEvent $event): void {
        $player = $event->getSender();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if(!$player->isLoaded()) {
            $event->cancel();
            return;
        }
        $message = $event->getCommand();
        if(strpos($message, "/") !== 0) {
            return;
        }
        if($this->core->getServerManager()->getWatchdogHandler()->isBlocked($player->getName())) {
            $ban = $this->core->getServerManager()->getWatchdogHandler()->getBlock($player->getName());
            $expiration = ($ban->getTime() + $ban->getExpiration()) - time();
            $timeString = Utils::secondsToTime($expiration);
            $message = Translation::getMessage("blockMessage", [
                "name" => TextFormat::RED . $ban->getEffector(),
                "reason" => TextFormat::YELLOW . $ban->getReason(),
                "time" => TextFormat::RED . $timeString
            ]);
            $player->sendMessage($message);
            $event->cancel();
            return;
        }
        if($player->hasNoClientPredictions() and $player->isFrozen()) {
            $value = false;
            $commands = ["/msg", "/w", "/tell", "/whisper", "/message", "/pm", "/m"];
            foreach($commands as $command) {
                if(strpos($message, $command) !== false) {
                    $value = true;
                }
            }
            if($value === true) {
                $player->sendMessage(Translation::getMessage("frozen", [
                    "name" => "You are"
                ]));
            }
        }
        if($player->getDataSession()->getRank()->getIdentifier() >= Rank::TRIAL_MODERATOR and $player->getDataSession()->getRank()->getIdentifier() <= Rank::OWNER) {
            $value = false;
            $commands = ["/r", "/reply", "/msg", "/w", "/tell", "/whisper", "/message", "/pm", "/m", "/feed", "/fly", "/spawn", "/pvp", "/faction", "/f", "/cf", "/ceinfo", "/crates", "/changelog", "/inbox", "/trade", "/kit", "/pvphud", "/repair", "/rename", "/sell", "/shop", "/skit", "/trash", "/vote", "/withdraw"];
            foreach($commands as $command) {
                if(strpos($message, $command) !== false) {
                    $value = true;
                }
            }
            if($value === false) {
                $this->core->getServer()->getAsyncPool()->submitTask(new CommandLogTask("[Faction] " . date("[n/j/Y][G:i:s]", time()). " {$player->getName()}: $message"));
            }
        }
        else {
            $this->core->getServer()->getAsyncPool()->submitTask(new PlayerCommandLogTask("[Faction] " . date("[n/j/Y][G:i:s]", time()) . " {$player->getName()}: {$event->getCommand()}"));
        }
    }

    /**
     * @priority HIGH
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if(!$player->isLoaded()) {
            $event->cancel();
            return;
        }
        if($player->hasVanished()) {
            $container = $event->getBlock()->getPosition()->getWorld()->getTile($event->getBlock()->getPosition());
            if($container instanceof Container) {
                $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
                $menu->setListener(InvMenu::readonly());
                $menu->getInventory()->setContents($container->getInventory()->getContents());
                $menu->send($player);
            }
            $event->cancel();
        }
    }

    /**
     * @priority LOWEST
     * @param EntityDamageEvent $event
     *
     * @throws TranslatonException
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if(!$entity instanceof NexusPlayer) {
            return;
        }
        if($entity->hasVanished()) {
            $event->cancel();
            return;
        }
        if($entity->hasNoClientPredictions() and $entity->isFrozen()) {
            $event->cancel();
            $entity->sendMessage(Translation::getMessage("frozen", [
                "name" => "You are"
            ]));
            if($event instanceof EntityDamageByEntityEvent) {
                $damager = $event->getDamager();
                if(!$damager instanceof NexusPlayer) {
                    return;
                }
                $damager->sendMessage(Translation::getMessage("frozen", [
                    "name" => $entity->getName() . " is"
                ]));
            }
        }
    }
}