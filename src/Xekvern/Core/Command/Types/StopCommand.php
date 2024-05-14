<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\Server;
use Xekvern\Core\Nexus;

class StopCommand extends Command {

    /**
     * StopCommand constructor.
     */
    public function __construct() {
        parent::__construct("stop", "Restart the server.", "/stop");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof ConsoleCommandSender or $sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            /** @var NexusPlayer $player */
            foreach(Server::getInstance()->getOnlinePlayers() as $player) {
                if($player->isTagged()) {
                    $player->combatTag(false);
                }
                $player->transfer("hub.nexuspe.net", 19132);
            }
            foreach(Nexus::getInstance()->getServer()->getWorldManager()->getWorlds() as $level) {
                $level->save(true);
            }
            foreach(Nexus::getInstance()->getServer()->getOnlinePlayers() as $player) {
                if($player instanceof NexusPlayer) {
                    if($player->isLoaded()) {
                        $player->getDataSession()->saveData();
                    }
                }
            }
            foreach(Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getFactions() as $faction) {
                $faction->update();
            }
            foreach(Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getClaims() as $claim) {
                $claim->update();
            }
            Server::getInstance()->shutdown();
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}