<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Combat\Outpost\Command;

use libs\muqsit\arithmexp\Util;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Combat\Outpost\Forms\OutpostInfoForm;
use Xekvern\Core\Utils\Utils;

class OutpostCommand extends Command {

    /**
     * OutpostCommand constructor.
     */
    public function __construct() {
        parent::__construct("outpost", "Find the outpost location.", "/outpost");
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
        $outpostArena = Nexus::getInstance()->getPlayerManager()->getCombatHandler()->getOutpostArena();
        //$x = $outpostArena->getSecondPosition()->getX();
        //$y = $outpostArena->getSecondPosition()->getY();
        //$z = $outpostArena->getSecondPosition()->getZ();
        //$sender->sendMessage(TextFormat::BOLD . TextFormat::AQUA . "Outpost Information");
        //$sender->sendMessage(TextFormat::YELLOW . "World: " . TextFormat::WHITE . "Warzone (/pvp)");
        //$sender->sendMessage(TextFormat::YELLOW . "Location: " . TextFormat::WHITE . $x . " " . $y . " " . $z);
        //$sender->sendMessage(TextFormat::YELLOW . "Control Time: " . TextFormat::WHITE . Utils::secondsToCD($outpostArena->getCaptureProgress()));
        $sender->sendForm(new OutpostInfoForm());
        return;
    }
}