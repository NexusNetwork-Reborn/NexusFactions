<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Task\TeleportTask;
use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class TeleportAskCommand extends Command
{

    /**
     * TeleportAskCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("tpa", "Ask to teleport to someone.", "/tp[a|accept|deny] <player:target>", ["tpaccept", "tpdeny"]);
        $this->registerArgument(0, new TargetArgument("player"));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (!isset($args[0])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));; 
            return;
        }
        $player = $this->getCore()->getServer()->getPlayerByPrefix($args[0]);
        if (!$player instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("invalidPlayer"));; 
            return;
        }
        if ($sender->isTeleporting() === true) {
            $sender->sendMessage(Translation::getMessage("alreadyTeleporting", [
                "name" => "You are"
            ]));; 
            return;
        }
        if ($player->isTeleporting() === true) {
            $sender->sendMessage(Translation::getMessage("alreadyTeleporting", [
                "name" => "{$player->getName()} is"
            ]));;
            return;
        }
        switch ($commandLabel) {
            case "tpa":
                if ($sender->isRequestingTeleport($player)) {
                    $sender->sendMessage(Translation::getMessage("alreadyRequest"));
                    return;
                }
                $sender->addTeleportRequest($player);
                $sender->sendMessage(Translation::getMessage("requestTeleport", [
                    "name" => "You have",
                    "player" => TextFormat::YELLOW . $player->getName()
                ]));
                $player->sendMessage(Translation::getMessage("requestTeleport", [
                    "name" => TextFormat::YELLOW . $sender->getName() . TextFormat::GRAY . " has",
                    "player" => "you"
                ]));
                break;
            case "tpaccept":
                if (!$player->isRequestingTeleport($sender)) {
                    $sender->sendMessage(Translation::getMessage("didNotRequest"));
                    return;
                }
                $player->removeTeleportRequest($sender);
                $player->sendMessage(Translation::getMessage("acceptRequest"));
                $this->getCore()->getScheduler()->scheduleRepeatingTask(new TeleportTask($player, $sender->getPosition(), 5), 20);
                break;
            case "tpdeny":
                if (!$player->isRequestingTeleport($sender)) {
                    $sender->sendMessage(Translation::getMessage("didNotRequest"));
                    return;
                }
                $player->removeTeleportRequest($sender);
                $player->sendMessage(Translation::getMessage("denyRequest"));
                break;
            default:
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
                break;
        }
    }
}