<?php

namespace Xekvern\Core\Server\Watchdog\Forms;

use Xekvern\Core\Server\Watchdog\PunishmentEntry;
use libs\form\CustomForm;
use libs\form\element\Label;
use pocketmine\utils\TextFormat;

class PunishHistoryForm extends CustomForm {

    /**
     * PunishListForm constructor.
     *
     * @param PunishmentEntry[][][] $entries
     */
    public function __construct(array $entries) {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Punish";
        $elements = [];
        foreach($entries as $type => $list) {
            foreach($list as $reason => $anotherList) {
                $elements[] = new Label($reason, "Violations for \"$reason\": " . count($anotherList));
            }
        }
        parent::__construct($title, $elements);
    }
}