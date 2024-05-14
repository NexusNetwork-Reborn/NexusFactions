<?php

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Player\Combat\Boss\Boss;
use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\utils\TextFormat;

class BossEgg extends ClickableItem {

    const BOSS_EGG = "BossEgg";
    const BOSS_TYPE = "BossType";

    /**
     * BossEgg constructor.
     */
    public function __construct(string $type = "Alien") {
        $customName = TextFormat::RESET . TextFormat::DARK_RED . TextFormat::BOLD . "Boss Summoner";
        $lore = [];
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::RED . "Boss: " . TextFormat::WHITE . $type;
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Click to spawn boss.";
        parent::__construct(VanillaItems::ZOMBIE_SPAWN_EGG(), $customName, $lore, [], [
            self::BOSS_EGG => new StringTag(self::BOSS_EGG),
            self::BOSS_TYPE => new StringTag($type)
        ]);
    }

    /**
     * @param NexusPlayer $player
     * @param Inventory $inventory
     * @param Item $item
     * @param CompoundTag $tag
     * @param int $face
     * @param Block $blockClicked
     *
     * @throws TranslatonException
     */
    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void {
        if($player->getWorld()->getFolderName() !== "bossarena") {
            $player->sendMessage(Translation::getMessage("canOnlySpawnInArena"));
            return;
        }
        $areaManager = $player->getCore()->getServerManager()->getAreaHandler();
        $area = $areaManager->getAreaByPosition($player->getPosition());
        if($area !== null) {
            if($area->getPvpFlag() === false) {
                $player->sendMessage(Translation::getMessage("canOnlySpawnInArena"));
                return;
            }
        }
        foreach($player->getWorld()->getEntities() as $entity) {
            if($entity instanceof Boss) {
                $player->sendMessage(Translation::getMessage("activeBoss"));
                return;
            }
        }
        $ps = $blockClicked->getPosition();
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
        Nexus::getInstance()->getPlayerManager()->getCombatHandler()->spawnBoss($tag->getString(BossEgg::BOSS_TYPE), new Location($ps->getX(), $ps->getY() + 1, $ps->getZ(), $ps->getWorld(), 0, 0));
    }

    /**
     * @return int
     */
    public function getMaxStackSize(): int {
        return 1;
    }   
}