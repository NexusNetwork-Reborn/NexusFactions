<?php

declare(strict_types=1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Task\TeleportTask;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class HomeCommand extends Command {

    /**
     * HomeCommand constructor.
     */
    public function __construct() {
        parent::__construct("home", "Teleport to a home");
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
            if(isset($args[0])) {
                $home = $sender->getDataSession()->getHome($args[0]);
                if($home === null) {
                    $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "HOMES:");
                    $sender->sendMessage(TextFormat::WHITE . implode(", ", array_keys($sender->getDataSession()->getHomes())));
                    return;
                }
                $this->getCore()->getScheduler()->scheduleRepeatingTask(new TeleportTask($sender, $home, 5), 20);
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
