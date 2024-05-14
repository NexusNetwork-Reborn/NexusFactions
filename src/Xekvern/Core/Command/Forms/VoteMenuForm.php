<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Forms;

use Xekvern\Core\Command\Task\CheckVoteTask;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class VoteMenuForm extends MenuForm {

    /**
     * VoteMenuForm constructor.
     */
    public function __construct() {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Vote Menu";
        $text = "What would you like to do?.";
        $options = [];
        $options[] = new MenuOption("Check for vote");
        $options[] = new MenuOption("Vote Shop");
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $option = $this->getOption($selectedOption);
        switch($option->getText()) {
            case "Check for vote":
                if($player->hasVoted()) {
                    $player->sendMessage(Translation::getMessage("alreadyVoted"));
                    $player->playErrorSound();
                    return;
                }
                if($player->isCheckingForVote()) {
                    $player->sendMessage(Translation::getMessage("checkingVote"));
                    return;
                }
                Server::getInstance()->getAsyncPool()->increaseSize(2);
                Server::getInstance()->getAsyncPool()->submitTaskToWorker(new CheckVoteTask($player->getName()), 1);
                break;
            case "Vote Shop":
                $player->sendForm(new VoteShopForm($player));
                break;
        }
    }
}