<?php

namespace Xekvern\Core\Server\NPC\Types;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\NPC\NPC;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Server\Item\Types\Lootbox;
use Xekvern\Core\Server\NPC\Inventory\FeaturedLootboxInventory;
use Xekvern\Core\Server\Price\Inventory\ShopMainInventory;
use Xekvern\Core\Utils\Utils;

class LootboxMerchant extends NPC {

    /**
     * LootboxMerchant constructor.
     */
    public function __construct() {
        $path = Nexus::getInstance()->getDataFolder() . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . "lootboxmerchant.png";
        $skin = Utils::createSkin(Utils::getSkinDataFromPNG($path));
        $position = new Position(37.3973, 55, -217.6436, Server::getInstance()->getWorldManager()->getDefaultWorld());
        $nameTag = $this->updateNameTag();
        parent::__construct($skin, $position, $nameTag);
    }

    /**
     * @param Player $player
     */
    public function tick(Player $player): void {
        if($this->hasSpawnedTo($player)) {
            $this->setNameTag($player);
        }
    }

    /**
     * @return string
     */
    public function updateNameTag(): string {
        $this->nameTag = TextFormat::BOLD . TextFormat::RED . "L" . TextFormat::GOLD . "o" . TextFormat::YELLOW . "o" . TextFormat::GREEN . "t" . TextFormat::AQUA . "b" . TextFormat::LIGHT_PURPLE . "o" . TextFormat::DARK_AQUA . "x " . TextFormat::RED . "Merchant\n" . TextFormat::GRAY . "View " . TextFormat::WHITE . "Lootbox: Husky";
        return $this->nameTag;
    }

    /**
     * @param Player $player
     */
    public function tap(Player $player): void {
        if($player instanceof NexusPlayer) {
            if ((time() - $this->spam) > 2) {
                (new FeaturedLootboxInventory())->send($player);
                $this->spam = time();
            } else {
                $player->sendTip(TextFormat::RED . "On Cooldown!");
            }
        }
    }
}