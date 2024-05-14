<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Quest\Forms;

use libs\form\CustomForm;
use libs\form\element\Label;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;

class QuestInfoForm extends CustomForm {

    /**
     * QuestInfoForm constructor.
     * @param NexusPlayer $player
     * @param string $quest
     */
    public function __construct(NexusPlayer $player, string $quest) {
        $title = TextFormat::BOLD . TextFormat::AQUA . $quest;
        $quest = Nexus::getInstance()->getPlayerManager()->getQuestHandler()->getQuest($quest);
        $session = Nexus::getInstance()->getPlayerManager()->getQuestHandler()->getSession($player);
        $elements = [];
        $elements[] = new Label("Description", "Description: " . $quest->getDescription());
        $progress = $session->getQuestProgress($quest);
        $target = $quest->getTargetValue();
        if ($progress === -1) {
            $progress = $target;
        }
        $elements[] = new Label("Progress", "Progress: $progress/$target");
        $elements[] = new Label("Difficulty", "Difficulty: " . $quest->getDifficultyName());
        $elements[] = new Label("Reward", "Reward: " . $quest->getDifficulty() . " quest points");
        parent::__construct($title, $elements);
    }

    /**
     * @param Player $player
     */
    public function onClose(Player $player): void {
        $player->sendForm(new QuestListForm());
    }
}