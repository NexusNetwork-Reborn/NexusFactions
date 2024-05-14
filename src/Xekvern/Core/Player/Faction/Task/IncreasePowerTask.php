<?php
declare(strict_types=1);

namespace Xekvern\Core\Player\Faction\Task;

use Xekvern\Core\Nexus;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Faction\FactionHandler;
use Xekvern\Core\Player\NexusPlayer;

class IncreasePowerTask extends Task {

    /** @var FactionHandler */
    private $manager;

    /**
     * IncreasePowerTask constructor.
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
        foreach($this->manager->getFactions() as $faction) {
            if($faction->getOnlineMembers() > 1) {
                foreach($faction->getOnlineMembers() as $members) {
                    if(!$members instanceof NexusPlayer) {
                        return;
                    }
                    if($faction->getValue() < 2500000) {
                        return;
                    }
                    $calculatedPower = (int)Nexus::getInstance()->getPlayerManager()->getFactionHandler()->calculatePower($faction->getValue());
                    $members->getDataSession()->addToPower($calculatedPower);
                    $members->sendMessage(TextFormat::BOLD . TextFormat::GOLD . "(Faction)" . TextFormat::RESET . TextFormat::GRAY . "All online members have received " . TextFormat::BOLD . TextFormat::GOLD . $calculatedPower . " Power" . TextFormat::RESET . TextFormat::GRAY . " due to the value of the faction.");
                }
            }
        }
    }
}