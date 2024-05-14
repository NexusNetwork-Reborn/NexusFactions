<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Forms;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\FormIcon;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class TagListForm extends MenuForm {

    /**
     * TagListForm constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player) {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Tags";
        $text = "Select a tag.";
        $icon = new FormIcon("https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/b/be/Name_Tag.png", FormIcon::IMAGE_TYPE_URL);
        $tags = $player->getDataSession()->getTags();
        $options = [];
        foreach($tags as $tag) {
            $options[] = new MenuOption($tag . "\n" . TextFormat::RESET . TextFormat::BLACK . "(Click to equip)", $icon);
        }
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     *
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $tag = explode("\n", $this->getOption($selectedOption)->getText())[0];
        $player->getDataSession()->setCurrentTag($tag);
        $player->sendMessage(Translation::getMessage("tagSetSuccess", [
            "tag" => $tag
        ]));
        return;
    }
}