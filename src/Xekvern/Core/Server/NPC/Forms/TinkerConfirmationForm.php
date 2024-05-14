<?php

namespace Xekvern\Core\Server\NPC\Forms;

use Xekvern\Core\Player\NexusPlayer;
use libs\form\ModalForm;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\Types\MythicalDust;

class TinkerConfirmationForm extends ModalForm
{

    /**
     * TinkerConfirmationForm constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player)
    {
        $item = $player->getInventory()->getItemInHand();
        $percentage = $this->calculateSuccessPercentage($item);
        $title = TextFormat::BOLD . TextFormat::AQUA . "Tinker";
        $text = "That item is looking mighty fine today. I'll give you a $percentage percent Mythical Dust. Will you accept my offer?";
        parent::__construct($title, $text);
    }

    /**
     * @param Player $player
     * @param bool $choice
     */
    public function onSubmit(Player $player, bool $choice): void
    {
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if ($choice == true) {
            $item = $player->getInventory()->getItemInHand();
            $player->getInventory()->removeItem($item);
            $item = new MythicalDust($this->calculateSuccessPercentage($item));
            $player->getInventory()->addItem($item->getItemForm());
            return;
        }
        return;
    }

    /**
     * @param Item $item
     *
     * @return int
     */
    public function calculateSuccessPercentage(Item $item): int
    {
        $percentage = 0;
        foreach ($item->getEnchantments() as $enchantment) {
            switch ($enchantment->getType()->getRarity()) {
                case Rarity::COMMON:
                    $percentage += round($enchantment->getLevel() * 0.25);
                    break;
                case Rarity::UNCOMMON:
                    $percentage += round($enchantment->getLevel() * 0.45);
                    break;
                case Rarity::RARE:
                    $percentage += round($enchantment->getLevel() * 0.75);
                    break;
                case Rarity::MYTHIC:
                    $percentage += round($enchantment->getLevel() * 1);
                    break;
                case Enchantment::RARITY_GODLY:
                    $percentage += round($enchantment->getLevel() * 0.2);
                    break;
            }
        }
        return ($percentage > 100) ? 100 : $percentage;
    }
}