<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\utils\TextFormat;

class SetHomeCommand extends Command {

    /**
     * SetHomeCommand constructor.
     */
    public function __construct() {
        parent::__construct("sethome", "Set a home", "/sethome <name: string>");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof NexusPlayer) {
            if(count($sender->getDataSession()->getHomes()) >= $sender->getDataSession()->getRank()->getHomeLimit()) {
                $sender->sendTitle(TextFormat::BOLD . TextFormat::RED . "Home Limit", TextFormat::GRAY . "You have reached the max home limit");
                $sender->sendMessage(Translation::getMessage("maxReached"));                 
            }
            if($sender->getGamemode() === GameMode::SPECTATOR()) {
                $sender->sendMessage(Translation::getMessage("noPermission"));              
                return;
            }
            if($sender->hasVanished()) {
                $sender->sendMessage(Translation::getMessage("noPermission"));
                return;
            }
            if(isset($args[0])) {
                $home = $sender->getDataSession()->getHome($args[0]);
                if($home !== null) {
                    $sender->sendMessage(Translation::getMessage("homeExist"));
                    return;
                }
                $sender->sendMessage(Translation::getMessage("setHome"));
                $sender->getDataSession()->addHome($args[0], $sender->getPosition());
                return;
            }
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}