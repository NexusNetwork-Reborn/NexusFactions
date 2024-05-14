<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Forms;

use Xekvern\Core\Command\Task\CheckVoteTask;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use libs\form\MenuForm;
use libs\form\MenuOption;
use muqsit\invmenu\InvMenu;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ItemInfoForm extends MenuForm {

    /** @var InvMenu */
    private InvMenu $menu;

    /**
     * ItemInfoForm constructor.
     */
    public function __construct(Item $item, InvMenu $menu) {
        $title = $item->getCustomName();
        $text =  implode("\n", $item->getLore());
        $this->menu = $menu;
        $options = [];
        $options[] = new MenuOption(TextFormat::BOLD . TextFormat::RED . "Back\n" . TextFormat::RESET . TextFormat::GRAY . "Back to previous menu");
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
                $player->sendDelayedWindow($this->menu);
                break;
        }
    }
}