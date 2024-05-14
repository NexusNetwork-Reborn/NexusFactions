<?php
declare(strict_types=1);

namespace Xekvern\Core\Server\Rewards;

use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Rewards\Types\LootboxRewards;
use Xekvern\Core\Server\Rewards\Types\MonthlyRewards;

class RewardsManager {

    /** @var Nexus */
    private $core;

    /** @var LootboxRewards[] */
    private $lootboxes = [];

    /** @var MonthlyRewards[][] */
    private $monthlys = [];

    /**
     * RewardsManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $this->init();
    }

    public function init(): void {
        
    }

    /**
     * @param LootboxRewards $rewards
     *
     * @throws RewardsException
     */
    public function registerLootbox(LootboxRewards $rewards): void {
        if(isset($this->lootboxes[$rewards->getName()])) {
            throw new RewardsException("Attempted to override a lootbox with the name of \"{$rewards->getName()}\".");
        }
        $this->lootboxes[$rewards->getName()] = $rewards;
    }

    /**
     * @param string $name
     *
     * @return LootboxRewards
     */
    public function getLootbox(string $name): LootboxRewards {
        return $this->lootboxes[$name];
    }

    /**
     * @param MonthlyRewards $rewards
     *
     * @throws RewardsException
     */
    public function registerMonthly(MonthlyRewards $rewards): void {
        if(isset($this->monthlys[$rewards->getYear()][$rewards->getMonth()])) {
            throw new RewardsException("Attempted to override a monthly with the year of \"{$rewards->getYear()}\" and the month of \"{$rewards->getMonth()}\".");
        }
        $this->monthlys[$rewards->getYear()][$rewards->getMonth()] = $rewards;
    }

    /**
     * @param int $year
     * @param string $month
     *
     * @return MonthlyRewards
     */
    public function getMonthly(int $year, string $month): MonthlyRewards {
        return $this->monthlys[$year][$month] ?? $this->monthlys[2022][MonthlyRewards::BACK_TO_SCHOOL];
    }
}