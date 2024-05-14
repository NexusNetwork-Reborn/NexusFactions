<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class PingCommand extends Command {

    /**
     * PingCommand constructor.
     */
    public function __construct() {
        parent::__construct("ping", "Check ping.", "/ping [player: target]");
        $this->registerArgument(0, new TargetArgument("player", true));
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
        if(isset($args[0])) {
            $player = $this->getCore()->getServer()->getPlayerExact($args[0]);
            if($player === null) {
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                return;
            }
        }
        if(isset($player)) {
            $ping = $player->getNetworkSession()->getPing();
            $name = $player->getName() . "'s";
        }
        else {
            $ping = $sender->getNetworkSession()->getPing();
            $name = "Your";
        }
        $sender->sendMessage(TextFormat::DARK_RED . "$name ping: " .  TextFormat::WHITE . "$ping milliseconds");
    }
}