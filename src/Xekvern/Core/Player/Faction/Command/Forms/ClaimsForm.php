<?php
declare(strict_types=1);

namespace Xekvern\Core\Faction\Command\Forms;

use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Nexus;
use libs\form\CustomForm;
use libs\form\element\Label;
use pocketmine\utils\TextFormat;

class ClaimsForm extends CustomForm {

    /**
     * FlagsMenuForm constructor.
     *
     * @param Faction $faction
     */
    public function __construct(Faction $faction) {
        $title = TextFormat::BOLD . TextFormat::AQUA . $faction->getName();
        $options = [];
        $claims = Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getClaimsOf($faction);
        $options[] = new Label("ClaimInfo", "Your faction has used " . count($claims) . "/" . $faction->getClaimLimit() . " claims");
        $list = [];
        $number = 0;
        foreach($claims as $claim) {
            $number++;
            $x = $claim->getChunkX() << 4;
            $z = $claim->getChunkZ() << 4;
            $list[] = "Claim #$number (X = $x, Z = $z)";
        }
        $options[] = new Label("ClaimList", implode("\n", $list));
        parent::__construct($title, $options);
    }
}