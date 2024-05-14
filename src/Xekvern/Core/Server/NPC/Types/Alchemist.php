<?php

namespace Xekvern\Core\Server\NPC\Types;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\NPC\NPC;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\NPC\Forms\AlchemistConfirmationForm;
use Xekvern\Core\Server\NPC\Forms\AlchemistConfirmationForm2;
use Xekvern\Core\Utils\Utils;

class Alchemist extends NPC {

    /**
     * Alchemist constructor.
     */
    public function __construct() {
        $path = Nexus::getInstance()->getDataFolder() . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . "alchemist.png";
        $skin = Utils::createSkin(Utils::getSkinDataFromPNG($path));
        $position = new Position(-43.4835, 55, -291.0569, Server::getInstance()->getWorldManager()->getDefaultWorld());
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
        $this->nameTag = TextFormat::BOLD . TextFormat::YELLOW . "Alchemist\n" . TextFormat::GRAY . "(Interact Me)";
        return $this->nameTag;
    }

    /**
     * @param Player $player
     */
    public function tap(Player $player): void {
        if($player instanceof NexusPlayer) {
            $item = $player->getInventory()->getItemInHand();
            if ($item->hasEnchantments()) {
                foreach ($player->getInventory()->getContents() as $i) {
                    $tag = $i->getNamedTag()->getTag(CustomItem::CUSTOM);
                    if ($tag !== null and $i->getTypeId() === ItemTypeIds::SUGAR) {
                        $player->sendForm(new AlchemistConfirmationForm2());
                        return;
                    }
                }
            }
            $tag = $item->getNamedTag();
            if ($tag === null or $item->getTypeId() !== VanillaItems::ENCHANTED_BOOK()->getTypeId()) {
                if ((time() - $this->spam) > 2) {
                    $player->playErrorSound();
                    $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Invalid Item", TextFormat::RESET . TextFormat::GRAY . "You must have enchantment book or an enchanted item!");
                    $this->spam = time();
                } else {    
                    $player->sendTip(TextFormat::RED . "On Cooldown!");
                }
                return;
            }
            $player->sendForm(new AlchemistConfirmationForm($player));
        }
    }
}