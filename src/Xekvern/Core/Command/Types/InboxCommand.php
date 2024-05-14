<?php

declare(strict_types=1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;

class InboxCommand extends Command {

    /**
     * InboxCommand constructor.
     */
    public function __construct() {
        parent::__construct("inbox", "Open inbox inventory", "/inbox", ["rewards"]);
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
        if(isset($args[0]) and $args[0] === "clear") {
            $sender->getDataSession()->getInbox()->getInventory()->clearAll();
            $sender->sendMessage(Translation::GREEN . "You have cleared your inbox.");
            return;
        }
        $sender->sendMessage(TextFormat::YELLOW . TextFormat::BOLD. "TIP: " . TextFormat::RESET . TextFormat::YELLOW . "Use /inbox clear to DELETE ALL of your items in your inbox.");
        $sender->getDataSession()->sendInboxInventory();
    }
}