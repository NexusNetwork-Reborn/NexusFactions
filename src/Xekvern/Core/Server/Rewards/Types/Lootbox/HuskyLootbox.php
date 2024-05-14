<?php
declare(strict_types=1);

namespace Xekvern\Core\Server\Rewards\Types\Lootbox;

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Rewards\Types\LootboxRewards;

class VoteLootbox extends LootboxRewards {

    const NAME = "Vote";

    /**
     * CrashLandingLootbox constructor.
     */
    public function __construct() {
        $rewards = [];
        $jackpot = [];
        $bonus = [];
        $coloredName = TextFormat::GREEN . TextFormat::BOLD . "Vote";
        $lore = "Obtained from voting at bit.ly/3m3AOdp";
        $rewardCount = 1;
        $display = VanillaBlocks::IRON()->asItem();
        parent::__construct(self::NAME, $coloredName, $lore, $rewardCount, $display, $rewards, $jackpot, $bonus);
    }
}