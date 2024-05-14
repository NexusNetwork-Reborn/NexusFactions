<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\entity\object\ItemEntity;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\Server;

class ClearlagCommand extends Command {

    /**
     * ClearlagCommand constructor.
     */
    public function __construct() {
        parent::__construct("clearlag", "Clear lag command.");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if((!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) or (!$sender instanceof NexusPlayer)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $sender->sendMessage(Translation::GREEN . "Successfully despawned all types of item entities!");
        $this->clearItems();
        return;
    }

    public function clearItems(): void {
        $count = 0;
        foreach(Server::getInstance()->getWorldManager()->getWorlds() as $lvl) {
            foreach($lvl->getEntities() as $en) {
                if($en instanceof ItemEntity) {
                    $count += 1;
                    $en->flagForDespawn();
                }
            }
        }
    }
}
