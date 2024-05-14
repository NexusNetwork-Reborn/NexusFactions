<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Forms;

use Xekvern\Core\Nexus;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;

class StaffTeleportForm extends MenuForm
{

    /**
     * StaffTeleportForm constructor.
     */
    public function __construct() {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Staff Teleport";
        $text = "You are currently in staffmode choose a player to spectate on.";
        $options = [];
        foreach(Server::getInstance()->getOnlinePlayers() as $online) {
            $options[] = new MenuOption($online->getName());
        }
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        $option = $this->getOption($selectedOption);
        $text = $option->getText();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $target = Nexus::getInstance()->getServer()->getPlayerByPrefix($text);
        if(!$target instanceof NexusPlayer) {
            $player->sendMessage(Translation::RED . "The player you tried to teleport to is not found on the server!");
            return;
        }
        Nexus::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(Nexus::getInstance()->getServer(), Nexus::getInstance()->getServer()->getLanguage()), "tp " . $player->getName() . " " . $target->getName());
    }
}