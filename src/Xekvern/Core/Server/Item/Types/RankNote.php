<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\block\Block;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\BlazeShootSound;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Translation\Translation;

class RankNote extends CustomItem {

    const RANK = "RankId";

    /**
     * RankNote constructor.
     *
     * @param int $amount
     * @param string $withdrawer
     */
    public function __construct(Rank $rank) {
        $customName = TextFormat::RESET . $rank->getColoredName() . TextFormat::RESET . TextFormat::WHITE . " Rank Note" . TextFormat::RESET . TextFormat::GRAY . " (Right-Click)";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "This note will grant you a rank that has";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "multiple perks to attain.";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::AQUA . "Hint: " . TextFormat::GRAY . "Tap anywhere to claim this rank.";
        parent::__construct(VanillaItems::PAPER(), $customName, $lore,        
        [
            new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(50), 1)
        ], [
            self::RANK => new IntTag($rank->getIdentifier())
        ]);
    }

    /**
     * @param NexusPlayer $player
     * @param Inventory $inventory
     * @param Item $item
     * @param CompoundTag $tag
     * @param int $face
     * @param Block $blockClicked
     */
    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void {
        $rank = $tag->getInt(self::RANK);
        $rank = Nexus::getInstance()->getPlayerManager()->getRankHandler()->getRankByIdentifier($rank);
        if($rank->getIdentifier() <= $player->getDataSession()->getRank()->getIdentifier()) {
            $player->playErrorSound();
            $player->sendMessage(Translation::RED . "Your current rank is better than this!");
            return;
        }
        $player->playXpLevelUpSound();
        $player->getDataSession()->setRank($rank);
        $player->sendMessage(Translation::getMessage("claimRank", [
            "name" => $rank->getColoredName()
        ]));
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }
}