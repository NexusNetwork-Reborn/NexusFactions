<?php

namespace Xekvern\Core\Server\NPC\Types;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\NPC\NPC;
use pocketmine\item\ItemTypeIds;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Command\Forms\ChangeLogForm;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\NPC\Forms\AlchemistConfirmationForm;
use Xekvern\Core\Server\NPC\Forms\AlchemistConfirmationForm2;
use Xekvern\Core\Utils\Utils;

class Updater extends NPC {

    /**
     * Updater constructor.
     */
    public function __construct() {
        $path = Nexus::getInstance()->getDataFolder() . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . "updater.png";
        $skin = Utils::createSkin(Utils::getSkinDataFromPNG($path));
        $position = new Position(-4.5477, 127.0625, -212.5435, Server::getInstance()->getWorldManager()->getDefaultWorld());
        $nameTag = $this->updateNameTag();
        parent::__construct($skin, $position, $nameTag);
    }

    /**
     * @param Player $player
     */
    public function tick(Player $player): void {
        if($this->hasSpawnedTo($player)) {
            $this->setNameTag($player);
        }
    }

    /**
     * @return string
     */
    public function updateNameTag(): string {
        $this->nameTag = TextFormat::BOLD . TextFormat::GREEN . "Newspaper Boy\n" . TextFormat::GRAY . "(Interact Me)";
        return $this->nameTag;
    }

    /**
     * @param Player $player
     */
    public function tap(Player $player): void {
        if($player instanceof NexusPlayer) {
            $player->sendForm(new ChangeLogForm());
        }
    }
}