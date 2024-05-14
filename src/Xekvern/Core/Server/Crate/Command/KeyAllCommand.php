<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Crate\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class KeyAllCommand extends Command {

    /**
     * KeyAllCommand constructor.
     */
    public function __construct() {
        parent::__construct("keyall", "Give crate keys to all players.", "/keyall <crate: string> <amount: int>");
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
            if(!isset($args[1])) {
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
                return;
            }
            $crate = $this->getCore()->getServerManager()->getCrateHandler()->getCrate($args[0]);
            if($crate === null) {
                $sender->sendMessage(Translation::getMessage("invalidCrate"));
                return;
            }
            $amount = is_numeric($args[1]) ? (int)$args[1] : 1;
            /** @var NexusPlayer $player */
            foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
                if($player->isLoaded() === false) {
                    continue;
                }
                $player->getDataSession()->addKeys($crate, $amount);
            }
            $this->getCore()->getServer()->broadcastMessage(Translation::getMessage("keyAll", [
                "name" => TextFormat::AQUA . $sender->getName(),
                "amount" => TextFormat::YELLOW . $amount,
                "type" => TextFormat::GRAY . $crate->getName()
            ]));
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}