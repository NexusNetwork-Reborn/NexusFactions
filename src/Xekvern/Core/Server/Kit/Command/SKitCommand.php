<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Kit\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Kit\Forms\KitListForm;
use Xekvern\Core\Server\Kit\Inventory\KitListInventory;

class SKitCommand extends Command {

    /**
     * SKitCommand constructor.
     */
    public function __construct() {
        parent::__construct("skit", "Manage your sacred kits");
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
        $fundHandler = Nexus::getInstance()->getServerManager()->getFundHandler();
        if ($fundHandler->getMergeFund("Sacred Kits")->isFunded() === false) {
            $sender->sendMessage(Translation::getMessage("notReachedGoal"));
            return;
        }
        (new KitListInventory($sender, "Sacred Kits", $this->getCore()->getServerManager()->getKitHandler()->getSacredKits()))->send();
    }
}