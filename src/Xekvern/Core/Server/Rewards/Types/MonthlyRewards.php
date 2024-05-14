<?php
declare(strict_types=1);

namespace Xekvern\Core\Server\Rewards\Types;

use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Server\Rewards\Rewards;

class MonthlyRewards extends Rewards {

    const JANUARY = "New Year";
    const FEBRUARY = "February";
    const MARCH = "March";
    const APRIL = "April";
    const MAY = "May";
    const JUNE = "June";
    const JULY = "July";
    const AUGUST = "August";
    const BACK_TO_SCHOOL = "Back To School";
    const SEPTEMBER = "September";
    const OCTOBER = "October";
    const HALLOWEEN = "Halloween";
    const NOVEMBER = "November";
    const DECEMBER = "December";
    const HOLIDAY = "Holiday";

    /** @var string */
    protected $month;

    /** @var int */
    protected $year;

    /** @var string */
    protected $coloredName;

    /** @var Reward[] */
    protected $adminItems = [];

    /** @var Reward[] */
    protected $cosmetics = [];

    /** @var Reward[] */
    protected $treasureItems = [];

    /** @var Reward[] */
    protected $bonus = [];

    /**
     * MonthlyRewards constructor.
     *
     * @param string $month
     * @param int $year
     * @param string $coloredName
     * @param array $adminItems
     * @param array $cosmetics
     * @param array $treasureItems
     * @param array $bonus
     */
    public function __construct(string $month, int $year, string $coloredName, array $adminItems, array $cosmetics, array $treasureItems, array $bonus) {
        $this->month = $month;
        $this->year = $year;
        $this->coloredName = $coloredName;
        $this->adminItems = $adminItems;
        $this->cosmetics = $cosmetics;
        $this->treasureItems = $treasureItems;
        $this->bonus = $bonus;
        parent::__construct($this->getAllRewards());
    }

    /**
     * @return string
     */
    public function getMonth(): string {
        return $this->month;
    }

    /**
     * @return int
     */
    public function getYear(): int {
        return $this->year;
    }

    /**
     * @return string
     */
    public function getColoredName(): string {
        return $this->coloredName;
    }

    /**
     * @return Reward[]
     */
    public function getAdminItems(): array {
        return $this->adminItems;
    }

    /**
     * @return Reward[]
     */
    public function getCosmetics(): array {
        return $this->cosmetics;
    }

    /**
     * @return Reward[]
     */
    public function getTreasureItems(): array {
        return $this->treasureItems;
    }

    /**
     * @return Reward[]
     */
    public function getBonus(): array {
        return $this->bonus;
    }

    /**
     * @return Reward[]
     */
    public function getAllRewards(): array {
        return array_merge($this->adminItems, $this->cosmetics, $this->treasureItems);
    }

    /**
     * @param int $loop
     * @param int $maxChance
     *
     * @return Reward
     */
    public function rollBonus(int $loop = 0, int $maxChance = 100): Reward {
        $chance = mt_rand(0, $maxChance);
        $index = array_rand($this->bonus);
        $reward = $this->bonus[$index];
        if($loop >= 10 and ($reward->getChance() / $maxChance) > 0.25) {
            return $reward;
        }
        if($reward->getChance() <= $chance) {
            return $this->rollBonus($loop + 1, $maxChance);
        }
        return $reward;
    }
}