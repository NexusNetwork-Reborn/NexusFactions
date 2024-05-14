<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Arguments\OnOrOffArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;

class VanishCommand extends Command
{

    /**
     * VanishCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("vanish", "Enable or disable vanish", "/vanish <on|off>");
        $this->registerArgument(0, new OnOrOffArgument("mode"));
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
        if ((!$sender instanceof NexusPlayer) or ((!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) and (!$sender->hasPermission("permission.staff")))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (!isset($args[0])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        switch ($args[0]) {
            case "on":
                $sender->vanish();
                $sender->sendMessage(Translation::getMessage("vanishToggle"));
                break;
            case "off":
                $sender->vanish(false);
                $sender->sendMessage(Translation::getMessage("vanishToggle"));
                break;
            default:
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
        }
    }
}