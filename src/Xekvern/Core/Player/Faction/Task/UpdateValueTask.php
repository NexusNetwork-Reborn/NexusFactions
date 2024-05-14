<?php
declare(strict_types=1);

namespace Xekvern\Core\Player\Faction\Task;

use Xekvern\Core\Nexus;
use pocketmine\scheduler\Task;
use Xekvern\Core\Player\Faction\FactionHandler;

class UpdateValueTask extends Task {

    /** @var FactionHandler */
    private $manager;

    /**
     * UpdateValueTask constructor.
     *
     * @param FactionHandler $manager
     */
    public function __construct(FactionHandler $manager) {
        $this->manager = $manager;
    }

    /**
     * @param int $tick
     */
    public function onRun(): void {
        $start = microtime(true);
        foreach($this->manager->getFactions() as $faction) {
            $claims = Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getClaimsOf($faction);
            $value = 0;
            foreach($claims as $claim) {
                $value += $claim->getValue();
            }
            $faction->setClaimValue($value);
        }
        $time = (microtime(true) - $start);
        Nexus::getInstance()->getLogger()->notice("[Auto Save] Successfully synced all factions claim values in " . ($time >= 1 ? round($time, 3) . "s" : round($time * 1000) . "ms"));
    }
}