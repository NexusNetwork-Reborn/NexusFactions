<?php
declare(strict_types=1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\Args\RawStringArgument;
use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\permission\DefaultPermissionNames;

class AddPermissionCommand extends Command
{

    /**
     * AddPermissionCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("addpermission", "Add a permission to a player.", "/addpermission <player:target> <permission: string>");
        $this->registerArgument(0, new TargetArgument("player"));
        $this->registerArgument(1, new RawStringArgument("permission"));
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
        if (($sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR) and $sender instanceof NexusPlayer) or $sender instanceof ConsoleCommandSender) {
            if (isset($args[1])) {
                $player = $sender->getServer()->getPlayerByPrefix($args[0]);
                if ($player instanceof NexusPlayer) {
                    $player->getDataSession()->addPermanentPermission((string)$args[1]);
                    $sender->sendMessage(Translation::getMessage("addPermission", [
                        "permission" => $args[1],
                        "name" => $player->getName()
                    ]));
                    return;
                }
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
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