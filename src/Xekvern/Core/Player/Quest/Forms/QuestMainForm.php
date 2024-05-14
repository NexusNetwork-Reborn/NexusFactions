<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Quest\Forms;

use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;

class QuestMainForm extends MenuForm {

    /**
     * QuestMainForm constructor.
     */
    public function __construct() {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Quest Menu";
        $text = "What would you like to look for?";
        $options = [];
        $options[] = new MenuOption("Active Quests");
        $options[] = new MenuOption("Quest Shop");
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $option = $this->getOption($selectedOption);
        switch ($option->getText()) {
            case "Active Quests":
                $player->sendForm(new QuestListForm());
                break;
            case "Quest Shop":
                $player->sendForm(new QuestShopForm($player));
                break;
        }
    }
}