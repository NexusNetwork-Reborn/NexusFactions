<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Forms;

use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class CEMenuForm extends MenuForm
{

    /**
     * CEInfoForm constructor.
     */
    public function __construct()
    {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Enchantments";
        $options = [];
        $options[] = new MenuOption("Armor");
        $options[] = new MenuOption("Sword");
        $options[] = new MenuOption("Pickaxe");
        $options[] = new MenuOption("Bow");
        parent::__construct($title, "Select a category to view an enchantment.", $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        $player->sendForm(new CEListForm($this->getOption($selectedOption)->getText()));
    }
}
