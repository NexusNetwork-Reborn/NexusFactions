<?php

namespace Xekvern\Core\Server\Watchdog\Forms;

use Xekvern\Core\Nexus;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Server\Watchdog\PunishmentEntry;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class PunishMenuForm extends MenuForm {

    /**
     * PunishMenuForm constructor.
     */
    public function __construct() {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Punish";
        $text = "Select an action.";
        $options = [];
        $options[] = new MenuOption("View bans");
        $options[] = new MenuOption("View mutes");
        $options[] = new MenuOption("View blocks");
        $options[] = new MenuOption("Ban");
        $options[] = new MenuOption("Mute");
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        $option = $this->getOption($selectedOption);
        switch($option->getText()) {
            case "View bans":
                $player->sendForm(new PunishListForm(Nexus::getInstance()->getServerManager()->getWatchdogHandler()->getBans()));
                break;
            case "View mutes":
                $player->sendForm(new PunishListForm(Nexus::getInstance()->getServerManager()->getWatchdogHandler()->getMutes()));
                break;
            case "View blocks":
                $player->sendForm(new PunishListForm(Nexus::getInstance()->getServerManager()->getWatchdogHandler()->getBlocks()));
                break;
            case "Ban":
                if(!$player->hasPermission("permission.mod")) {
                    $player->sendMessage(Translation::getMessage("noPermission"));
                    return;
                }
                $player->sendForm(new PunishActionForm(PunishmentEntry::BAN));
                break;
            case "Mute":
                $player->sendForm(new PunishActionForm(PunishmentEntry::MUTE));
                break;
        }
    }
}