<?php

namespace Xekvern\Core\Server\NPC\Forms;

use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilBreakSound;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\ItemHandler;

class AlchemistForm extends MenuForm
{

    /** @var Item */
    private Item $item;

    /** @var Item */
    private Item $enchantmentRemover;

    /**
     * AlchemistForm constructor.
     *
     * @param Item $item
     * @param Item $enchantmentRemover
     */
    public function __construct(Item $item, Item $enchantmentRemover)
    {
        $this->item = $item;
        $this->enchantmentRemover = $enchantmentRemover;
        $options = [];
        foreach ($item->getEnchantments() as $enchantment) {
            $options[] = new MenuOption(ItemHandler::getEnchantmentFormat($enchantment->getType(), TextFormat::AQUA, false));
        }
        parent::__construct(TextFormat::GREEN . TextFormat::BOLD . "Alchemist", TextFormat::WHITE . "Which enchantment would you like to remove?", $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        $inventory = $player->getInventory();
        $inventory->removeItem($this->item);
        $enchantment = StringToEnchantmentParser::getInstance()->parse($this->getOption($selectedOption)->getText());
        if ($enchantment === null) {
            return;
        }
        $this->item->removeEnchantment($enchantment, $this->item->getEnchantmentLevel($enchantment));
        $lore = [];
        foreach ($this->item->getEnchantments() as $enchantment) {
            if ($enchantment->getType() instanceof Enchantment) {
                $lore[] = TextFormat::RESET . ItemHandler::rarityToColor($enchantment->getType()->getRarity()) . $enchantment->getType()->getName() . " " . ItemHandler::getRomanNumber($enchantment->getLevel());
            }
        }
        $inventory->removeItem($this->enchantmentRemover);
        $this->item->setLore($lore);
        $inventory->addItem($this->item);
        $player->getWorld()->addSound($player->getPosition(), new AnvilBreakSound());
    }
}
