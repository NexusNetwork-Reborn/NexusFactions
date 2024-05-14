<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Kit\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use Xekvern\Core\Server\Kit\Forms\KitListForm;
use Xekvern\Core\Server\Kit\Inventory\KitListInventory;

class KitCommand extends Command {

    /**
     * KitCommand constructor.
     */
    public function __construct() {
        parent::__construct("kit", "Manage your kits");
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
        $sender->sendForm(new KitListForm($sender, $this->getCore()->getServerManager()->getKitHandler()->getKits()));
    }
}