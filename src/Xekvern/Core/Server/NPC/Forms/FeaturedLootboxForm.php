<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\NPC\Forms;

use Xekvern\Core\Player\NexusPlayer;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\NPC\Inventory\ViewLootboxInventory;

class FeaturedLootboxForm extends MenuForm {

    /** @var Item */
    private Item $item;

    /**
     * FeaturedLootboxForm constructor.
     */
    public function __construct(Item $item) {
        $title = $item->getCustomName();
        $text =  implode("\n", $item->getLore());
        $this->item = $item;
        $options = [];
        $options[] = new MenuOption(TextFormat::BOLD . TextFormat::AQUA . "View Loot\n" . TextFormat::RESET . TextFormat::GRAY . "View this lootbox's rewards");
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        switch($selectedOption) {
            case 0:
                $player->removeCurrentWindow();
                $player->sendDelayedWindow((new ViewLootboxInventory($this->item)));
                break;
        }
    }
}