<?php

namespace Xekvern\Core\Player\Gamble;

use Xekvern\Core\Player\Gamble\Task\DrawLotteryTask;
use Xekvern\Core\Player\Gamble\Task\RollCoinFlipTask;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;

class GambleHandler {

    const TICKET_PRICE = 10000;

    /** @var Nexus */
    private $core;

    /** @var CoinFlipEntry[] */
    private $coinFlips = [];

    /** @var string[] */
    private $coinFlipRecord = [];

    /** @var RollCoinFlipTask[] */
    private $activeCoinFlips = [];

    /** @var int[] */
    private $pot = [];

    /** @var DrawLotteryTask */
    private $drawer;

    /**
     * GambleHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $this->core->getServer()->getPluginManager()->registerEvents(new GambleEvents($core), $core);
        $this->drawer = new DrawLotteryTask($this);
        $core->getScheduler()->scheduleRepeatingTask($this->drawer, 20);
    }

    /**
     * @return CoinFlipEntry[]
     */
    public function getCoinFlips(): array {
        return $this->coinFlips;
    }

    /**
     * @param NexusPlayer $player
     *
     * @return CoinFlipEntry|null
     */
    public function getCoinFlip(NexusPlayer $player): ?CoinFlipEntry {
        return $this->coinFlips[$player->getUniqueId()->toString()] ?? null;
    }

    /**
     * @param NexusPlayer $player
     * @param CoinFlipEntry $entry
     */
    public function addCoinFlip(NexusPlayer $player, CoinFlipEntry $entry): void {
        if(isset($this->coinFlips[$player->getUniqueId()->toString()])) {
            return;
        }
        $this->coinFlips[$player->getUniqueId()->toString()] = $entry;
    }

    /**
     * @param NexusPlayer $player
     */
    public function removeCoinFlip(NexusPlayer $player): void {
        if(!isset($this->coinFlips[$player->getUniqueId()->toString()])) {
            return;
        }
        unset($this->coinFlips[$player->getUniqueId()->toString()]);
    }

    /**
     * @param RollCoinFlipTask $task
     */
    public function addActiveCoinFlip(RollCoinFlipTask $task): void {
        $this->activeCoinFlips[$task->getOwnerEntry()->getOwner()->getUniqueId()->toString()] = $task;
        $this->activeCoinFlips[$task->getTargetEntry()->getOwner()->getUniqueId()->toString()] = $task;
    }

    /**
     * @param RollCoinFlipTask $task
     */
    public function removeActiveCoinFlip(RollCoinFlipTask $task): void {
        unset($this->activeCoinFlips[$task->getOwnerEntry()->getOwner()->getUniqueId()->toString()]);
        unset($this->activeCoinFlips[$task->getTargetEntry()->getOwner()->getUniqueId()->toString()]);
    }

    /**
     * @param NexusPlayer $player
     *
     * @return RollCoinFlipTask|null
     */
    public function getActiveCoinFlip(NexusPlayer $player): ?RollCoinFlipTask {
        return $this->activeCoinFlips[$player->getUniqueId()->toString()] ?? null;
    }


    /**
     * @param NexusPlayer $player
     * @param $wins
     * @param $losses
     */
    public function getRecord(NexusPlayer $player, &$wins, &$losses): void {
        $record = $this->coinFlipRecord[$player->getName()];
        $reward = explode(":", $record);
        $wins = $reward[0];
        $losses = $reward[1];
    }

    /**
     * @param NexusPlayer $player
     */
    public function createRecord(NexusPlayer $player): void {
        $this->coinFlipRecord[$player->getName()] = "0:0";
    }

    /**
     * @param NexusPlayer $player
     */
    public function addWin(NexusPlayer $player): void {
        $record = $this->coinFlipRecord[$player->getName()];
        $reward = explode(":", $record);
        $wins = intval($reward[0]) + 1;
        $losses = $reward[1];
        $this->coinFlipRecord[$player->getName()] = "$wins:$losses";
    }

    /**
     * @param NexusPlayer $player
     */
    public function addLoss(NexusPlayer $player): void {
        $record = $this->coinFlipRecord[$player->getName()];
        $reward = explode(":", $record);
        $wins = $reward[0];
        $losses = intval($reward[1]) + 1;
        $this->coinFlipRecord[$player->getName()] = "$wins:$losses";
    }

    /**
     * @param NexusPlayer $player
     * @param int $draws
     */
    public function addDraws(NexusPlayer $player, int $draws) {
        if(!isset($this->pot[$player->getName()])) {
            $this->pot[$player->getName()] = $draws;
            return;
        }
        $this->pot[$player->getName()] += $draws;
    }

    /**
     * @param NexusPlayer $player
     *
     * @return int
     */
    public function getDrawsFor(NexusPlayer $player): int {
        if(!isset($this->pot[$player->getName()])) {
            $this->pot[$player->getName()] = 0;
        }
        return $this->pot[$player->getName()];
    }

    /**
     * @return int
     */
    public function getTotalDraws(): int {
        $amount = 0;
        foreach($this->pot as $entries) {
            $amount += $entries;
        }
        return $amount;
    }

    /**
     * @return int[]
     */
    public function getPot(): array {
        return $this->pot;
    }

    /**
     * @return string|null
     */
    public function draw(): ?string {
        $total = $this->getTotalDraws();
        if($total <= 0 or empty($this->pot)) {
            return null;
        }
        foreach($this->pot as $name => $draws) {
            if(mt_rand(1, $total) <= $draws) {
                return $name;
            }
        }
        return $this->draw();
    }

    /**
     * @return DrawLotteryTask
     */
    public function getDrawer(): DrawLotteryTask {
        return $this->drawer;
    }
}