<?php

namespace Xekvern\Core\Server\NPC\Forms;

use libs\form\ModalForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\nbt\tag\CompoundTag;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Types\EnchantmentRemover;
use Xekvern\Core\Server\Item\Utils\CustomItem;

class AlchemistConfirmationForm extends ModalForm
{
	/**
	 * AlchemistConfirmationForm constructor.
	 *
	 * @param NexusPlayer $player
	 */
	public function __construct(NexusPlayer $player) {
		$item = $player->getInventory()->getItemInHand();
		$tag = $item->getNamedTag(CustomItem::CUSTOM);
		if(!$tag instanceof CompoundTag) { return; }
		$enchantment = ItemHandler::getEnchantment($tag->getInt(EnchantmentBook::ENCHANT));
		$title = TextFormat::BOLD . TextFormat::GREEN . "Alchemist";
        $text = "Ah, yes. A " . ItemHandler::getEnchantmentFormat($enchantment, TextFormat::AQUA, false) . " book, what I was looking for. I'll be willing to trade a random enchantment remover. Will you accept my offer?";
        parent::__construct($title, $text);
    }

	/**
	 * @param Player $player
     * @param int $selectedOption
	 */
	public function onSubmit(Player $player, bool $choice) : void {
		if(!$player instanceof NexusPlayer) { return; }
		if($choice === true) {
			$item = $player->getInventory()->getItemInHand();
			$player->getInventory()->removeItem($item);
			$item = new EnchantmentRemover(mt_rand(1, 100));
			$player->getInventory()->addItem($item->getItemForm());
			return;
		}
		return;
	}
}