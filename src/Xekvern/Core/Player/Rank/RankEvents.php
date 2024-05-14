<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Rank;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\player\chat\LegacyRawChatFormatter;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Utils\Utils;

class RankEvents implements Listener {

    /** @var Nexus */
    private $core;

    /**
     * RankEvents constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }
    /**
     * @priority NORMAL
     * @param PlayerChatEvent $event
     */
    public function onPlayerChat(PlayerChatEvent $event): void {
        if($event->isCancelled()) {
            return;
        }
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if(!$player->isLoaded()) {
            return;
        }
        $event->cancel();
        $mode = $player->getChatMode();
        $faction = $player->getDataSession()->getFaction();
        if($faction === null and ($mode === NexusPlayer::FACTION or $mode === NexusPlayer::ALLY)) {
            $mode = NexusPlayer::PUBLIC;
            $player->setChatMode($mode);
        }
        if($mode === NexusPlayer::PUBLIC) {
            $message = $event->getMessage();
            if($player->getDisguiseRank() !== null) {
                $format = $player->getDisguiseRank()->getChatFormatFor($player, $message, [
                    "faction_rank" => $player->getDataSession()->getFactionRoleToString(),
                    "faction" => ($faction = $player->getDataSession()->getFaction()) !== null ? $faction->getName() : "",
                    "factionRanking" => ($faction = $player->getDataSession()->getFaction()) !== null ? TextFormat::GRAY . " [" . $this->core->getPlayerManager()->getFactionHandler()->formatRanking($faction->getName()). TextFormat::GRAY . "] " . TextFormat::RESET : "",
                    "kills" => $player->getDataSession()->getKills(),
                    "tag" => $player->getDataSession()->getCurrentTag()
                ]);
                /** @var NexusPlayer $onlinePlayer */
                foreach($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    if(!$onlinePlayer->isLoaded()) {
                        continue;
                    }
                    $onlinePlayer->sendMessage($format);
                }
                $this->core->getLogger()->info($format);
            }
            else {
                $format = $player->getDataSession()->getRank()->getChatFormatFor($player, $message, [
                    "faction_rank" => $player->getDataSession()->getFactionRoleToString(),
                    "faction" => ($faction = $player->getDataSession()->getFaction()) !== null ? $faction->getName() : "",
                    "factionRanking" => ($faction = $player->getDataSession()->getFaction()) !== null ? TextFormat::GRAY . " [" . $this->core->getPlayerManager()->getFactionHandler()->formatRanking($faction->getName()). TextFormat::GRAY . "] " . TextFormat::RESET : "",
                    "kills" => $player->getDataSession()->getKills(),
                    "tag" => $player->getDataSession()->getCurrentTag()
                ]);
                /** @var NexusPlayer $onlinePlayer */
                foreach($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    if(!$onlinePlayer->isLoaded()) {    
                        continue;
                    }
                    $onlinePlayer->sendMessage($format);
                }
                $this->core->getLogger()->info($format);
                $webhook_url = "https://discord.com/api/webhooks/1203169624581996574/qfOeSd-fDv-gXt6Mrp3vwXQ1iae1b6ri6NvDMtElRSPkfmgQKu9Cxw9JCizlL-R-HHKq";
                $webhook_username = $player->getDataSession()->getRank() . $player->getName();
                $webhookdata = array(
                    "username" => $webhook_username,
                    "avatar_url" => "https://minecraftfaces.com/wp-content/bigfaces/big-steve-face.png", // Replace with the URL of the player's face image
                    "content" => str_replace('@', '#', $message)
                );
                $json_data = json_encode($webhookdata);
                $ch = curl_init($webhook_url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error: ' . curl_error($ch);
                }
                curl_close($ch);
            }
            return;
        }
        $event->cancel();
        if($mode === NexusPlayer::STAFF) {
            /** @var NexusPlayer $staff */
            foreach($this->core->getServer()->getOnlinePlayers() as $staff) {
                if(!$staff->isLoaded()) {
                    continue;
                }
                $rank = $staff->getDataSession()->getRank();
                if($rank->getIdentifier() >= Rank::TRIAL_MODERATOR and $rank->getIdentifier() <= Rank::OWNER) {
                    $staff->sendMessage(TextFormat::DARK_GRAY . "[" . $player->getDataSession()->getRank()->getColoredName() . TextFormat::RESET . TextFormat::DARK_GRAY . "] " . TextFormat::WHITE . $player->getName() . TextFormat::GRAY . ": " . $event->getMessage());
                }
            }
            return;
        }
        if($player->getChatMode() === NexusPlayer::FACTION) {
            $onlinePlayers = $faction->getOnlineMembers();
            foreach($onlinePlayers as $onlinePlayer) {
                if(!$onlinePlayer->isLoaded()) {
                    continue;
                }
                $onlinePlayer->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::BOLD . TextFormat::RED . "FC" . TextFormat::RESET . TextFormat::DARK_GRAY . "] " . TextFormat::WHITE . $player->getName() . TextFormat::GRAY . ": " . $event->getMessage());
            }
        }
        else {
            $allies = $faction->getAllies();
            $onlinePlayers = $faction->getOnlineMembers();
            foreach($allies as $ally) {
                if(($ally = $this->core->getPlayerManager()->getFactionHandler()->getFaction($ally)) === null) {
                    continue;
                }
                $onlinePlayers = array_merge($ally->getOnlineMembers(), $onlinePlayers);
            }
            foreach($onlinePlayers as $onlinePlayer) {
                if(!$onlinePlayer->isLoaded()) {
                    continue;
                }
                $onlinePlayer->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::BOLD . TextFormat::GOLD . "AC" . TextFormat::RESET . TextFormat::DARK_GRAY . "] " . TextFormat::WHITE . $player->getName() . TextFormat::GRAY . ": " . $event->getMessage());
            }
        }
    }

    function formatPing($ping) {
        if ($ping >= 0 && $ping <= 100) {
            return TextFormat::GREEN . $ping;
        } elseif ($ping >= 101 && $ping <= 250) {
            return TextFormat::YELLOW . $ping;
        } else {
            return TextFormat::RED . $ping;
        }
    }

    /**
     * @priority NORMAL
     * @param EntityRegainHealthEvent $event
     */
    public function onEntityRegainHealth(EntityRegainHealthEvent $event): void {
        if($event->isCancelled()) {
            return;
        }
        if($event->getRegainReason() === EntityRegainHealthEvent::CAUSE_MAGIC) {
            $event->setAmount($event->getAmount() * 1.25);
        }
        $player = $event->getEntity();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if(!$player->isLoaded()) {
            return;
        }
        $hp = round($player->getHealth(), 1);
        if($player->getCESession()->isHidingHealth()) {
            $hp = TextFormat::OBFUSCATED . $hp . TextFormat::RESET;
        }
        $player->setScoreTag(TextFormat::WHITE . $hp . TextFormat::RED . TextFormat::BOLD . " HP " . TextFormat::RESET . TextFormat::DARK_GRAY . "| " . TextFormat::BOLD . Utils::formatPing($player->getNetworkSession()->getPing()) . "ms");
    }

    /**
     * @priority NORMAL
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        if($event->isCancelled()) {
            return;
        }
        $player = $event->getEntity();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if(!$player->isLoaded()) {
            return;
        }
        $hp = round($player->getHealth(), 1);
        if($player->getCESession()->isHidingHealth()) {
            $hp = TextFormat::OBFUSCATED . $hp . TextFormat::RESET;
        }
        $player->setScoreTag(TextFormat::WHITE . $hp . TextFormat::RED . TextFormat::BOLD . " HP " . TextFormat::RESET . TextFormat::DARK_GRAY . "| " . TextFormat::BOLD . Utils::formatPing($player->getNetworkSession()->getPing()) . "ms");
    }
}