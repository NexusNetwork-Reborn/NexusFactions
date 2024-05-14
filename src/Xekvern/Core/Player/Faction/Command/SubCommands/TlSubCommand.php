<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands;

use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;

class TlSubCommand extends SubCommand
{

    /**
     * TlCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("tl", "/faction tl");
        $this->registerArgument(0, new IntegerArgument("page = 1", true));
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
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        if ($sender->getDataSession()->getFaction() === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        $faction = $sender->getDataSession()->getFaction();
        if ($sender->getDataSession()->getFaction() !== null) {
            foreach ($faction->getOnlineMembers() as $member) {
                $member->sendMessage(Translation::BLUE . "It looks like " . TextFormat::LIGHT_PURPLE .  $sender->getName() . TextFormat::GRAY . " needs help! XYZ: " . TextFormat::YELLOW . (float)$sender->getPosition()->x . ", " . (float)$sender->getPosition()->getY() . ", " . (float)$sender->getPosition()->getZ());
                return;
            }
        }
    }
}
