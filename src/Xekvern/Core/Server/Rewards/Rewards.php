<?php
declare(strict_types=1);

namespace Xekvern\Core\Server\Rewards;

use Xekvern\Core\Server\Crate\Reward;

class Rewards {

    /** @var Reward[] */
    protected $rewards;

    /**
     * Rewards constructor.
     *
     * @param Reward[] $rewards
     */
    public function __construct(array $rewards) {
        $this->rewards = $rewards;
    }

    /**
     * @param int $loop
     * @param int $maxChance
     *
     * @return Reward
     */
    public function getReward(int $loop = 0, int $maxChance = 100): Reward {
        $chance = mt_rand(0, $maxChance);
        $reward = $this->rewards[array_rand($this->rewards)];
        if($loop >= 10 and ($reward->getChance() / $maxChance) > 0.25) {
            return $reward;
        }
        if($reward->getChance() <= $chance) {
            return $this->getReward($loop + 1, $maxChance);
        }
        return $reward;
    }

    /**
     * @return Reward[]
     */
    public function getRewards(): array {
        return $this->rewards;
    }
}