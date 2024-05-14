<?php

namespace Xekvern\Core\Server\NPC\Types;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\NPC\NPC;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Server\Auction\Inventory\AuctionPageInventory;
use Xekvern\Core\Server\NPC\Forms\TinkerConfirmationForm;
use Xekvern\Core\Utils\Utils;

class Tinker extends NPC {

    /**
     * Tinker constructor.
     */
    public function __construct() {
        $path = Nexus::getInstance()->getDataFolder() . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . "tinker.png";
        $skin = Utils::createSkin(Utils::getSkinDataFromPNG($path));
        $position = new Position(-14.5235, 55, -244.1519, Server::getInstance()->getWorldManager()->getDefaultWorld());
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
        $this->nameTag = TextFormat::BOLD . TextFormat::DARK_BLUE . "Tinkerer\n" . TextFormat::GRAY . "(Interact Me)";
        return $this->nameTag;
    }

    /**
     * @param Player $player
     */
    public function tap(Player $player): void {
        if($player instanceof NexusPlayer) {
            $item = $player->getInventory()->getItemInHand();
            if ($item->hasEnchantments()) {
                $player->sendForm(new TinkerConfirmationForm($player));
            } else {
                if ((time() - $this->spam) > 2) {
                    $player->playErrorSound();
                    $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Invalid Item", TextFormat::RESET . TextFormat::GRAY . "You must have an enchanted item!");
                    $this->spam = time();
                } else {    
                    $player->sendTip(TextFormat::RED . "On Cooldown!");
                }
            }
        }
    }
}