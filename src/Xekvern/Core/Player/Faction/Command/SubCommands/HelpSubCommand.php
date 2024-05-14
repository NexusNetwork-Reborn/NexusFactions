<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class HelpSubCommand extends SubCommand {

    /**
     * HelpSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("help", "/faction help <1-6>");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        switch($args[1]) {
            case 1:
                $help = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::AQUA . "Faction Help " . TextFormat::RESET . TextFormat::GRAY . "(Page 1 of 6)",
                    TextFormat::GREEN . " /faction create " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Create a faction.",
                    TextFormat::GREEN . " /faction disband " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Disband your faction.",
                    TextFormat::GREEN . " /faction ally " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Request to ally with another faction.",
                    TextFormat::GREEN . " /faction claim " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Claim a chunk of land.",
                    TextFormat::GREEN . " /faction claimnear " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Claim nearby chunks of land.",
                ]);
                $sender->sendMessage($help);
                break;
            case 2:
                $help = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::AQUA . "Faction Help " . TextFormat::RESET . TextFormat::GRAY . "(Page 2 of 6)",
                    TextFormat::GREEN . " /faction chat " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Switch to the faction chat mode.",
                    TextFormat::GREEN . " /faction announce " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Announce a message to the whole faction.",
                    TextFormat::GREEN . " /faction deposit " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Deposit money into your faction.",
                    TextFormat::GREEN . " /faction withdraw " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Withdraw from faction balance.",
                    TextFormat::GREEN . " /faction claims " . TextFormat::GRAY . " - " . TextFormat::WHITE . " View your already claimed chunks.",
                ]);
                $sender->sendMessage($help);
                break;
            case 3:
                $help = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::AQUA . "Faction Help " . TextFormat::RESET . TextFormat::GRAY . "(Page 3 of 6)",
                    TextFormat::GREEN . " /faction info " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Show info about a faction.",
                    TextFormat::GREEN . " /faction invite " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Invite a player.",
                    TextFormat::GREEN . " /faction join " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Accept a faction invite.",
                    TextFormat::GREEN . " /faction promote " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Promote a faction member.",
                    TextFormat::GREEN . " /faction demote " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Demote a faction member.",
                ]);
                $sender->sendMessage($help);
                break;
            case 4:
                $help = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::AQUA . "Faction Help " . TextFormat::RESET . TextFormat::GRAY . "(Page 4 of 6)",
                    TextFormat::GREEN . " /faction payout " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Set your payout email.",
                    TextFormat::GREEN . " /faction leader " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Give faction leadership to another member.",
                    TextFormat::GREEN . " /faction kick " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Kick a faction member.",
                    TextFormat::GREEN . " /faction map " . TextFormat::GRAY . " - " . TextFormat::WHITE . " View faction map.",
                    TextFormat::GREEN . " /faction flags " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Edit your flags.",
                ]);
                $sender->sendMessage($help);
                break;
            case 5:
                $help = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::AQUA . "Faction Help " . TextFormat::RESET . TextFormat::GRAY . "(Page 5 of 6)",
                    TextFormat::GREEN . " /faction home " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Teleport to your faction home.",
                    TextFormat::GREEN . " /faction sethome " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Set a faction home.",
                    TextFormat::GREEN . " /faction leave " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Leave a faction.",
                    TextFormat::GREEN . " /faction top " . TextFormat::GRAY . " - " . TextFormat::WHITE . " View top factions.",
                ]);
                $sender->sendMessage($help);
                break;
            case 6:
                $help = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::AQUA . "Faction Help " . TextFormat::RESET . TextFormat::GRAY . "(Page 6 of 6)",
                    TextFormat::GREEN . " /faction unclaim " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Unclaim a chunk of land.",
                    TextFormat::GREEN . " /faction vault " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Open the vault of your faction.",
                    TextFormat::GREEN . " /faction tl " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Notify your faction associates your location.",
                    TextFormat::GREEN . " /faction overclaim " . TextFormat::GRAY . " - " . TextFormat::WHITE . " Overtake another faction's claim.",
                ]);
                $sender->sendMessage($help);
                break;
            default:
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
                break;
        }
    }
}
