<?php

namespace Xekvern\Core\Command\Forms;

use Xekvern\Core\Server\Item\Types\CustomTag;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\CustomForm;
use libs\form\CustomFormResponse;
use libs\form\element\Input;
use pocketmine\item\Durable;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilUseSound;

class RenameItemForm extends CustomForm {

    /**
     * RenameItemForm constructor.
     */
    public function __construct() {
        $elements = [];
        $title = TextFormat::BOLD . TextFormat::AQUA . "Rename";
        $text = "What would you like to rename your item?";
        $elements[] = new Input("CustomName", $text);
        parent::__construct($title, $elements);
    }

    /**
     * @param Player $player
     * @param CustomFormResponse $data
     *
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, CustomFormResponse $data): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $item = $player->getInventory()->getItemInHand();
        $tag = $item;
        if((!$item instanceof Durable) or $tag === new CustomTag) {
            $player->sendMessage(Translation::getMessage("invalidItem"));
            return;
        }
        $value = $data->getString("CustomName");
        $name = str_replace("&", TextFormat::ESCAPE, $value);
        if(strlen($name) > 30) {
            $player->sendMessage(Translation::getMessage("nameTooLong"));
            return;
        }
        $cost = (int)strlen($name) * 10000;
        if($player->getDataSession()->getBalance() >= $cost) {
            $item->setCustomName($name);
            $player->getInventory()->setItemInHand($item);
            $player->getDataSession()->subtractFromBalance($cost);
            $player->sendMessage(Translation::getMessage("successRename"));
            $player->getWorld()->addSound($player->getEyePos(), new AnvilUseSound());
            return;
        }
        $player->sendMessage(Translation::getMessage("notEnoughMoney"));
        return;
    }
}