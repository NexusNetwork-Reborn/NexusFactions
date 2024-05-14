<?php
declare(strict_types=1);

namespace Xekvern\Core\Server\Rewards\Types;

use pocketmine\item\Item;
use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Server\Rewards\Rewards;

class LootboxRewards extends Rewards {

    const NAME = "";

    /** @var string */
    protected $name;

    /** @var string */
    protected $coloredName;

    /** @var string */
    protected $lore;

    /** @var int */
    protected $rewardCount;

    /** @var Item */
    private $display;

    /** @var Reward[] */
    protected $jackpotLoot = [];

    /** @var Reward[] */
    protected $bonus = [];

    /**
     * LootboxRewards constructor.
     *
     * @param string $name
     * @param string $coloredName
     * @param string $lore
     * @param int $rewardCount
     * @param Item $display
     * @param array $rewards
     * @param array $jackpotLoot
     * @param array $bonus
     */
    public function __construct(string $name, string $coloredName, string $lore, int $rewardCount, Item $display, array $rewards, array $jackpotLoot, array $bonus) {
        $this->name = $name;
        $this->coloredName = $coloredName;
        $this->lore = $lore;
        $this->rewardCount = $rewardCount;
        $this->display = $display;
        $this->jackpotLoot = $jackpotLoot;
        $this->bonus = $bonus;
        parent::__construct($rewards);
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getColoredName(): string {
        return $this->coloredName;
    }

    /**
     * @return string
     */
    public function getLore(): string {
        return $this->lore;
    }

    /**
     * @return int
     */
    public function getRewardCount(): int {
        return $this->rewardCount;
    }

    /**
     * @return Item
     */
    public function getDisplay(): Item {
        return $this->display;
    }

    /**
     * @return Reward[]
     */
    public function getJackpotLoot(): array {
        return $this->jackpotLoot;
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
        return array_merge($this->rewards, $this->jackpotLoot);
    }

    /**
     * @param array $rewards
     * @param array $leftover
     *
     * @return array
     */
    public function getRoll(array $rewards, array $leftover = []): array {
        if(count($rewards) >= $this->rewardCount) {
            return $rewards;
        }
        if(count($rewards) <= 0) {
            $leftover = $this->getAllRewards();
        }
        $rewards[] = $this->pullReward($leftover);
        return $this->getRoll($rewards, $leftover);
    }

    /**
     * @param array $rewards
     * @param int $loop
     * @param int $maxChance
     *
     * @return Reward
     */
    private function pullReward(array &$rewards, int $loop = 0, int $maxChance = 100): Reward {
        $chance = mt_rand(0, $maxChance);
        $index = array_rand($rewards);
        $reward = $rewards[$index];
        if($loop >= 10 and ($reward->getChance() / $maxChance) > 0.25) {
            unset($rewards[$index]);
            return $reward;
        }
        if($reward->getChance() <= $chance) {
            return $this->getReward($loop + 1, $maxChance);
        }
        unset($rewards[$index]);
        return $reward;
    }
}