<?php

namespace Xekvern\Core\Server\NPC\Forms;

use libs\form\ModalForm;
use pocketmine\item\ItemTypeIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilBreakSound as SoundAnvilBreakSound;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\Types\EnchantmentRemover;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\NexusException;
use Xekvern\Core\Server\Item\ItemHandler;

class AlchemistConfirmationForm2 extends ModalForm {

    /**
     * AlchemistConfirmationForm2 constructor.
     */
    public function __construct() {
        $title = TextFormat::BOLD . TextFormat::GREEN . "Alchemist";
        $this->title = $title;
        $text = "Ok, I guess I will do this since you got an enchantment remover. The risk is high. A random enchantment could be removed. Are you willing to take the risk?";
        parent::__construct($title, $text);
    }

    /**
     * @param Player $player
     * @param bool $choice
     *
     * @throws NexusException
     */
    public function onSubmit(Player $player, bool $choice) : void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if($choice == true) {
            foreach($player->getInventory()->getContents() as $i) {
                $tag = $i->getNamedTag(CustomItem::CUSTOM);
                if($tag instanceof CompoundTag and isset($tag->getValue()[EnchantmentRemover::SUCCESS_PERCENTAGE]) and $i->getTypeId() === ItemTypeIds::SUGAR) {
                    $player->getInventory()->removeItem($i);
                    $success = $tag->getInt(EnchantmentRemover::SUCCESS_PERCENTAGE);
                    break;
                }
            }
            if (!isset($success)) {
                $player->sendMessage(Translation::getMessage("errorOccurred"));
                return;
            }
            $item = $player->getInventory()->getItemInHand();
            if(mt_rand(1, 100) <= $success) {
                $player->sendForm(new AlchemistForm( $item, $i));
            }
            else {
                $player->getInventory()->removeItem($item);
                $enchantments = $item->getEnchantments();
                $enchantment = $enchantments[array_rand($enchantments)];
                $item->removeEnchantment($enchantment->getType(), $enchantment->getLevel());
                $player->getInventory()->addItem(ItemHandler::setLoreForItem($item));
                $player->sendMessage(Translation::getMessage("enchantmentRemoverFail", [
                    "enchantment" => TextFormat::RED . ItemHandler::getEnchantmentFormat($enchantment->getType(), TextFormat::AQUA, false)
                ]));
                $player->getWorld()->addSound($player->getPosition(), new SoundAnvilBreakSound());
                return;
            }
        }
        return;
    }
}