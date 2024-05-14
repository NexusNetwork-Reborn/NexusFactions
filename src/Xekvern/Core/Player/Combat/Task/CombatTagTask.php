<?php

namespace Xekvern\Core\Player\Combat\Task;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\scheduler\Task;
use Xekvern\Core\Translation\Translation;

class CombatTagTask extends Task {

	/** @var NexusPlayer */
	protected $player;

	/**
	 * CombatTagTask Constructor.
	 * @param NexusPlayer $player
	 */
	public function __construct(NexusPlayer $player) {
		$this->player = $player;
		$player->combatTag(true);
		$player->sendMessage(Translation::getMessage("combatTag"));
	}

	/**
	 * @return void
	 */
	public function onRun() : void {
		$player = $this->player;
		if(!$player->isOnline()) {
			$this->getHandler()->cancel();
			return;
		}		
		if($player->isFlying()) {
			$player->setAllowFlight(false);
			$player->setFlying(false);
			return;
		}
		if(!$player->isTagged()) {
			$this->getHandler()->cancel();
			return;
		}
		if($player->combatTagTime() === 0) {
			$player->setCombatTagged(false);
			$player->setLastHit(null);
			$player->sendMessage(Translation::getMessage("noLongerInCombat"));
			$this->getHandler()->cancel();
		} else {
			$player->setCombatTagTime($player->combatTagTime() - 1);
		}
	}
}