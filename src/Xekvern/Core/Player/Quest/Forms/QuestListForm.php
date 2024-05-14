<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Quest\Forms;

use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;

class QuestListForm extends MenuForm {

    /**
     * QuestListForm constructor.
     */
    public function __construct() {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Active Quests";
        $text = "Which quest would you like to start?";
        $options = [];
        foreach (Nexus::getInstance()->getPlayerManager()->getQuestHandler()->getActiveQuests() as $quest) {
            $options[] = new MenuOption($quest->getName());
        }
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
        $player->sendForm(new QuestInfoForm($player, $option->getText()));
    }

    /**
     * @param Player $player
     */
    public function onClose(Player $player): void {
        $player->sendForm(new QuestMainForm());
    }
}