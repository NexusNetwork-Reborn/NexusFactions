<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands;

use pocketmine\block\Thin;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;

class InfoSubCommand extends SubCommand {

    /**
     * InfoSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("info", "/faction info [faction/player]", ["who"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (!$sender->isLoaded()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(!isset($args[1])) {
            if($sender->getDataSession()->getFaction() === null) {
                $sender->sendMessage(Translation::getMessage("beInFaction"));
                return;
            }
            $faction = $sender->getDataSession()->getFaction();
        }
        else {
            $player = $this->getCore()->getServer()->getPlayerExact($args[1]);
            if($player instanceof NexusPlayer) {
                $faction = $player->getDataSession()->getFaction();
            }
            else {
                $faction = $this->getCore()->getPlayerManager()->getFactionHandler()->getFaction($args[1]);
            }
            if($faction === null) {
                $sender->sendMessage(Translation::getMessage("invalidFaction"));
                return;
            }
        }
        $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . $faction->getName() . TextFormat::RESET . TextFormat::DARK_GRAY . " [" . TextFormat::GRAY . count($faction->getMembers()) . "/" . Faction::MAX_MEMBERS . TextFormat::DARK_GRAY . "]");
        $role = Faction::LEADER;
        $name = $faction->getName();
        $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("SELECT username FROM stats WHERE faction = ? and factionRole = ?");
        $stmt->bind_param("si", $name, $role);
        $stmt->execute();
        $stmt->bind_result($leader);
        $stmt->fetch();
        $stmt->close();
        $members = [];
        foreach($faction->getMembers() as $member) {
            /** @var NexusPlayer $player */
            if(($player = $this->getCore()->getServer()->getPlayerExact($member)) !== null) {
                if($player->isDisguise()) {
                    $members[] = TextFormat::WHITE . $player->getName();
                    continue;
                }
                $members[] = TextFormat::GREEN . $player->getName();
                continue;
            }
            $members[] = TextFormat::WHITE . $member;
        }
        $email = "Not set";
        if(!empty($faction->getPayoutEmail())) {
            $email = $faction->getPayoutEmail();
        }
        $info = implode(TextFormat::RESET . "\n", [
            TextFormat::RED . " Leader: " . TextFormat::WHITE . $leader,
            TextFormat::RED . " Ranking: " . TextFormat::GRAY . "[" . Nexus::getInstance()->getPlayerManager()->getFactionHandler()->formatRanking($faction->getName()). TextFormat::GRAY . "] ",
            TextFormat::RED . " Members: " . implode(TextFormat::GRAY . ", ", $members),
            TextFormat::RED . " Allies: " . TextFormat::WHITE . implode(", ", $faction->getAllies()),
            TextFormat::RED . " Power: " . TextFormat::WHITE . number_format($faction->getStrength()) . " STR",
            TextFormat::RED . " Value: " . TextFormat::WHITE . "$" . number_format($faction->getValue()),
            TextFormat::GRAY . " (" . Nexus::getInstance()->getPlayerManager()->getFactionHandler()->calculatePower($faction->getValue()) . " Power Increase every 45 MINUTES)",
            TextFormat::RED . " Balance: " . TextFormat::WHITE . "$" . number_format($faction->getBalance()),
        ]);
        $sender->sendMessage($info);
        $senderFaction = $sender->getDataSession()->getFaction();
        if(($senderFaction !== null and $senderFaction->getName() === $faction->getName() and $senderFaction->getPermissionsModule()->hasPermission($sender, PermissionsModule::PERMISSION_SEE_PAYOUT_EMAIL))
            or ($sender->getDataSession()->getRank()->getIdentifier() >= Rank::ADMIN and $sender->getDataSession()->getRank()->getIdentifier() <= Rank::OWNER)) {
            $sender->sendMessage(TextFormat::RED . " Payout Email (Hidden): " . TextFormat::WHITE . $email);
        }
    }
}