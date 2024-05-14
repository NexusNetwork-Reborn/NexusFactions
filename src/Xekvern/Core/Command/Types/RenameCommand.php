<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Forms\RenameItemForm;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\item\Durable;
use pocketmine\player\Player;
use Xekvern\Core\Player\NexusPlayer;

class RenameCommand extends Command {

    /**
     * RenameCommand constructor.
     */
    public function __construct() {
        parent::__construct("rename", "Rename an item", "/rename");
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
            $item = $sender->getInventory()->getItemInHand();
            if(!$item instanceof Durable) {
                $sender->sendMessage(Translation::getMessage("invalidItem"));
                return;
            }
            $sender->sendForm(new RenameItemForm());
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}