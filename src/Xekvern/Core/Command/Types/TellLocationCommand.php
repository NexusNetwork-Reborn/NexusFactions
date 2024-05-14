<?php

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Faction\Faction;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class TellLocationCommand extends Command {

    /**
     * TellLocationCommand constructor.
     */
    public function __construct() {
        parent::__construct("telllocation", "Broadcast your location to your faction members", "/telllocation", ["tl"]);
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
        $faction = $sender->getDataSession()->getFaction();
        if($faction === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        $message = TextFormat::LIGHT_PURPLE . $sender->getName() . "'s Current Location: ";
        $message .= "\n" . TextFormat::YELLOW . "  X: " . $sender->getPosition()->getFloorX();
        $message .= "\n" . TextFormat::YELLOW . "  Y: " . $sender->getPosition()->getFloorY();
        $message .= "\n" . TextFormat::YELLOW . "  Z: " . $sender->getPosition()->getFloorZ();
        $message .= "\n" . TextFormat::YELLOW . " World: " . $sender->getPosition()->getWorld()->getDisplayName();
        foreach($faction->getOnlineMembers() as $member) {
            $member->sendMessage($message);
        }
        $factionManager = Nexus::getInstance()->getPlayerManager()->getFactionHandler();
        foreach($faction->getAllies() as $ally) {
            $ally = $factionManager->getFaction($ally);
            if($ally !== null) {
                foreach($ally->getOnlineMembers() as $member) {
                    $member->sendMessage($message);
                }
            }
        }
    }
}