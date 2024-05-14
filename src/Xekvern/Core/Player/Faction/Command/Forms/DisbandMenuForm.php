<?php
declare(strict_types=1);

namespace Xekvern\Core\Player\Faction\Command\Forms;

use Xekvern\Core\Player\Faction\FactionException;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\MenuForm;
use libs\form\MenuOption;
use libs\utils\UtilsException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class DisbandMenuForm extends MenuForm {

    /** @var string */
    private $permission;

    /**
     * DisbandMenuForm constructor.
     *
     * @param string $permission
     */
    public function __construct() {
        $title = TextFormat::BOLD . TextFormat::RED . "Disband Faction";
        $text = TextFormat::RED . "Are you sure to do this action?\n\n" . TextFormat::RED . "Note: " . TextFormat::WHITE . "All your faction data will be lost!";
        $options = [];
        $options[] = new MenuOption("Yes");
        $options[] = new MenuOption("No");
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     *
     * @throws UtilsException
     * @throws FactionException
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        $option = $this->getOption($selectedOption);
        $text = $option->getText();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if($text === "Yes") {
            foreach ($player->getDataSession()->getFaction()->getOnlineMembers() as $member) {
                $member->sendMessage(Translation::RED . "Your faction has been disbanded! You are no longer in a faction.");
                $member->sendTitle(TextFormat::RED . TextFormat::BOLD . "Alert!", TextFormat::GRAY . $player->getDataSession()->getFaction()->getName() . " has been disbanded");
            }
            $player->getDataSession()->getFaction()->disband();
        }
        if($text === "No") {
            return;
        }
    }
}