<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Price\Forms;

use Xekvern\Core\Nexus;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ShopForm extends MenuForm {

    /**
     * ShopForm constructor.
     */
    public function __construct() {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Shop Menu";
        $text = "Select where you would like to shop.";
        $options = [];
        foreach(Nexus::getInstance()->getServerManager()->getPriceHandler()->getPlaces() as $place) {
            $options[] = new MenuOption($place->getName());
        }
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        $option = $this->getOption($selectedOption);
        $text = $option->getText();
        $player->sendForm(new ItemListForm(Nexus::getInstance()->getServerManager()->getPriceHandler()->getPlace($text)));
    }
}