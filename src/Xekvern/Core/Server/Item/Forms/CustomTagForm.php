<?php

namespace Xekvern\Core\Server\Item\Forms;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\CustomForm;
use libs\form\CustomFormResponse;
use libs\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilUseSound;

class CustomTagForm extends CustomForm
{

    /**
     * CustomTagForm constructor.
     */
    public function __construct()
    {
        $elements = [];
        $title = TextFormat::BOLD . TextFormat::AQUA . "Custom Tag";
        $text = "What would you like your tag to be? Must be within 16 characters, color is not included.";
        $elements[] = new Input("CustomName", $text);
        parent::__construct($title, $elements);
    }

    /**
     * @param Player $player
     * @param CustomFormResponse $data
     *
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, CustomFormResponse $data): void
    {
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $value = $data->getString("CustomName");
        $name = str_replace("&", TextFormat::ESCAPE, $value);
        if (strlen(TextFormat::clean($name)) > 16) {
            $player->sendMessage(Translation::getMessage("tagTooLong"));
            return;
        }
        if (!preg_match("/^[a-zA-Z]+$/", TextFormat::clean($name))) {
            $player->sendMessage(Translation::getMessage("onlyLetters"));
            return;
        }
        $player->getDataSession()->addTag($name);
        $player->getWorld()->addSound($player->getPosition(), new AnvilUseSound());
        $item = $player->getInventory()->getItemInHand();
        $player->getInventory()->setItemInHand($item->setCount($item->getCount() - 1));
    }
}