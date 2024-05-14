<?php

namespace Xekvern\Core\Player\Faction\Command\Task;

use libs\muqsit\arithmexp\Util;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Utils\Utils;

class OverclaimTask extends Task {

	/** @var NexusPlayer */
	protected $player;

	/** @var Faction */
	protected $faction;

    /** @var int */
    private $time = 90;

	/**
	 * OverclaimTask Constructor.
	 * @param NexusPlayer $player
	 */
	public function __construct(NexusPlayer $player, Faction $faction) {
		$this->player = $player;
        $this->faction = $faction;
	}

	/**
	 * @return void
	 */
	public function onRun() : void {
		$player = $this->player;
        $factionHandler = Nexus::getInstance()->getPlayerManager()->getFactionHandler();
		if(!$player->isOnline() or !$player->isAlive() or !$player->isLoaded()) {
			$this->getHandler()->cancel();
			return;
		}
        $this->time--;		
        $claim = $factionHandler->getClaimInPosition($player->getPosition());
        if($player->getDataSession()->getFaction() === null) {
            $this->getHandler()->cancel();
            $player->playErrorSound();
            $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Overclaim Failed", TextFormat::RESET . TextFormat::GRAY . "You are no longer in a faction!");
            return;
        }
        if ($claim === null or 
           !$claim->getFaction()->getName() === $this->faction->getName() or
           $claim->getFaction() === $player->getDataSession()->getFaction()
        ) {
            $this->getHandler()->cancel();
            $player->playErrorSound();
            $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Overclaim Failed", TextFormat::RESET . TextFormat::GRAY . "You are no longer in the claim!");
            return;
        }
        if ($this->time <= 0) { 
            $playerFaction = $player->getDataSession()->getFaction();
            foreach($playerFaction->getOnlineMembers() as $members) {
                $members->sendMessage(Translation::GREEN . "Our faction has overclaimed a claim from " . TextFormat::YELLOW . $claim->getFaction()->getName());
            }
            $player->sendTitle(TextFormat::BOLD . TextFormat::GREEN . "Overclaim Success", TextFormat::RESET . TextFormat::GRAY . "Successfully overtaken a faction claim");
            $player->playDingSound();
            foreach($this->faction->getOnlineMembers() as $members) {
                $members->sendMessage(Translation::RED . "One of our claims has been taken by another faction " . TextFormat::YELLOW . $player->getDataSession()->getFaction()->getName());
            }
            $factionHandler->overClaim($playerFaction, $claim);
            $this->getHandler()->cancel();
        } else {
            $player->sendTip(TextFormat::GREEN . "Overclaiming in " . Utils::secondsToTime($this->time));
        }
	}
}