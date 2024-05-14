<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Combat\Outpost\Forms;

use libs\form\CustomForm;
use libs\form\element\Label;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Utils\Utils;

class OutpostInfoForm extends CustomForm {

    /**
     * OutpostInfoForm constructor.
     */
    public function __construct() {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Outpost Information";
        $outpostArena = Nexus::getInstance()->getPlayerManager()->getCombatHandler()->getOutpostArena();
        $x = $outpostArena->getSecondPosition()->getX();
        $y = $outpostArena->getSecondPosition()->getY();
        $z = $outpostArena->getSecondPosition()->getZ();
        $changeLog = implode(TextFormat::RESET . "\n", [
            TextFormat::YELLOW . "Location:" . TextFormat::WHITE . $x . " " . $y . " " . $z,
            TextFormat::YELLOW . "World:" . TextFormat::WHITE . "Warzone (/pvp)",
            TextFormat::YELLOW . "Control Time:" . TextFormat::WHITE . Utils::secondsToCD($outpostArena->getCaptureProgress()),
            "",
            TextFormat::GRAY . "When holding the outpost the faction has the following perks:",
            "- All members will have x0.5 increase on selling items (/sell).",
            "- All members will receive an amount of 2 to 8 power by time of holding",
            "- The Faction will receive $250,000 on their balance by time of holding",
        ]);
        $elements = [];
        $elements[] = new Label("Changes",  $changeLog);
        parent::__construct($title, $elements);
    }
}