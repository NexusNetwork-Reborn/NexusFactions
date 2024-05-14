<?php

namespace Xekvern\Core\Server\Watchdog\Forms;

use Xekvern\Core\Server\Watchdog\PunishmentEntry;
use libs\form\CustomForm;
use libs\form\element\Label;
use libs\muqsit\arithmexp\Util;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Utils\Utils;

class PunishInfoForm extends CustomForm {

    /**
     * PunishInfoForm constructor.
     *
     * @param PunishmentEntry $entry
     */
    public function __construct(PunishmentEntry $entry) {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Punish";
        $elements = [];
        $elements[] = new Label("Victim", "Victim: " . $entry->getUsername());
        $elements[] = new Label("Effector", "Effector: " . $entry->getEffector());
        $elements[] = new Label("Reason", "Reason: " . $entry->getReason());
        $elements[] = new Label("Duration", "Duration: " . Utils::secondsToTime($entry->getExpiration()));
        $elements[] = new Label("Date", "Date: " . date("n/j/Y (G:i:s)", $entry->getTime()));
        parent::__construct($title, $elements);
    }
}