<?php

declare(strict_types=1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class AddGemsCommand extends Command
{

    /**
     * AddGemsCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("addgems", "Add gems to a player.", "/addgems <player:target> [amount: int]", ["givegems"]);
        $this->registerArgument(0, new TargetArgument("player"));
        $this->registerArgument(1, new IntegerArgument("amount"));
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
        if (!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $player = $this->getCore()->getServer()->getPlayerByPrefix($args[0]);
        if (!$player instanceof NexusPlayer) {
            $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("SELECT gems FROM users WHERE username = ?");
            $stmt->bind_param("s", $args[0]);
            $stmt->execute();
            $stmt->bind_result($gems);
            $stmt->fetch();
            $stmt->close();
            if ($gems === null) {
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                return;
            }
        }
        if ($player instanceof NexusPlayer) {
            if (!$player->isLoaded()) {
                $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("SELECT gems FROM users WHERE username = ?");
                $stmt->bind_param("s", $args[0]);
                $stmt->execute();
                $stmt->bind_result($gems);
                $stmt->fetch();
                $stmt->close();
                if ($gems === null) {
                    $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                    return;
                }
            }
        }
        if (!is_numeric($args[1])) {
            $sender->sendMessage(Translation::getMessage("notNumeric"));
            return;
        }
        if (isset($gems)) {
            $amount = (int)$args[1];
            $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("UPDATE users SET gems = gems + ? WHERE username = ?");
            $stmt->bind_param("is", $amount, $args[0]);
            $stmt->execute();
            $stmt->close();
        } else {
            /** @var NexusPlayer $player */
            $player->getDataSession()->addGems((int)$args[1]);
        }
        $name = $player instanceof NexusPlayer ? $player->getName() : $args[0];
        $sender->sendMessage(Translation::GREEN . "You have sucessfully added " . TextFormat::BOLD . TextFormat::GREEN . number_format((int)$args[1]) . " Gems" . TextFormat::RESET . TextFormat::GRAY . " to " . TextFormat::GOLD . $name);
    }
}
