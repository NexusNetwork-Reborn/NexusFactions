<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Forms;

use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\ItemHandler;

class CEListForm extends MenuForm
{

    const TYPE_TO_FLAG_MAP = [
        "Armor" => "Armor",
        "Tools" => "Tools",
        "Pickaxe" => "Tools",
        "Sword" => "Sword",
        "Boots" => "Armor",
        "Bow" => "Bow",
        "Chestplate" => "Armor",
        "Helmet" => "Armor",
    ];

    /**
     * CEListForm constructor.
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $title = TextFormat::BOLD . TextFormat::AQUA . $type;
        $options = [];
        $enchantments = [];
        foreach (ItemHandler::getEnchantments() as $enchantment) {
            if ($enchantment instanceof Enchantment and (!in_array($enchantment, $enchantments))) {
                if (ItemHandler::flagToString($enchantment->getPrimaryItemFlags()) === "None") {
                } else {
                    if (self::TYPE_TO_FLAG_MAP[ItemHandler::flagToString($enchantment->getPrimaryItemFlags())] === self::TYPE_TO_FLAG_MAP[$type]) {
                        $enchantments[] = $enchantment;
                        $options[] = new MenuOption($enchantment->getName());
                    }
                }
            }
        }
        parent::__construct($title, "Select an enchantment to view.", $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        $enchantment = ItemHandler::getEnchantment($this->getOption($selectedOption)->getText());
        if ($enchantment instanceof Enchantment) {
            $player->sendForm(new CEInfoForm($enchantment));
        }
    }
}
