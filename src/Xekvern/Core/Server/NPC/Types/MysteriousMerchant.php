<?php

namespace Xekvern\Core\Server\NPC\Types;

use cosmicpe\npcdialogue\dialogue\NpcDialogueButton;
use cosmicpe\npcdialogue\NpcDialogueBuilder;
use cosmicpe\npcdialogue\NpcDialogueManager;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\NPC\NPC;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Server\Auction\Inventory\AuctionPageInventory;
use Xekvern\Core\Server\NPC\Inventory\MysteriousItemsInventory;
use Xekvern\Core\Utils\Utils;

class MysteriousMerchant extends NPC {

    /**
     * MysteriousMerchant constructor.
     */
    public function __construct() {
        $path = Nexus::getInstance()->getDataFolder() . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . "mysteriousmerchant.png";
        $skin = Utils::createSkin(Utils::getSkinDataFromPNG($path));
        $position = new Position(12.3893, 55, -283.5453, Server::getInstance()->getWorldManager()->getDefaultWorld());
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
        $this->nameTag = TextFormat::BOLD . TextFormat::DARK_AQUA . "Mysterious Merchant\n" . TextFormat::GRAY . "(Interact Me)";
        return $this->nameTag;
    }

    /**
     * @param Player $player
     */
    public function tap(Player $player): void {
        $path = Nexus::getInstance()->getDataFolder() . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . "mysteriousmerchant.png";
        $skin = Utils::createSkin(Utils::getSkinDataFromPNG($path));
        if($player instanceof NexusPlayer) {
            $dialogue = NpcDialogueBuilder::create()
            ->setName("Mysterious Merchant")
            ->setText("Hello there! I'm offering such items in exchange of gems.\n\nYou may purchase gems @ " . TextFormat::AQUA . "store.nexuspe.net" . "\n\n" . TextFormat::RESET . "You currently have " . TextFormat::BOLD . TextFormat::GREEN . number_format($player->getDataSession()->getGems()) . " GEMS")
            ->setSkinNpcTexture($skin)
        	->addSimpleButton("View Offers", function(NexusPlayer $player) : void {
                $player->removeCurrentWindow();
                $player->sendDelayedWindow(new MysteriousItemsInventory());
            })
            ->build();
            NpcDialogueManager::send($player, $dialogue);
        }
    }
}