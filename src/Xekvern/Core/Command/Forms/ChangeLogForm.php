<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Forms;

use libs\form\CustomForm;
use libs\form\element\Label;
use pocketmine\utils\TextFormat;

class ChangeLogForm extends CustomForm {

    /**
     * ChangeLogForm constructor.
     */
    public function __construct() {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Change Log";
        $changeLog = implode(TextFormat::RESET . "\n", [
            "Greetings adventurers! The arrival of Chapter II Season II has finally come! We are bringing upon many exciting changes for you! We are bringing upon many exciting changes for you!",
            "- Player Level System",
            "- Revision to Appearance of UIs",
            "- More Custom Enchants",
            "- More bug fixes",
            "- Outpost System",
            "- Improved Anti Cheat",
            "- Enchantment Scrolls and Limits",
            "Enjoy the BETA!"
        ]);
        $elements = [];
        $elements[] = new Label("Changes",  $changeLog);
        parent::__construct($title, $elements);
    }
}