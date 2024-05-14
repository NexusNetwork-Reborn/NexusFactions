<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Forms;

use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Server\Item\Types\ChestKit;
use Xekvern\Core\Server\Item\Types\CrateKeyNote;
use Xekvern\Core\Server\Item\Types\HolyBox;
use Xekvern\Core\Server\Item\Types\MonthlyCrate;
use Xekvern\Core\Server\Item\Types\SacredStone;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\MenuForm;
use libs\form\MenuOption;
use Xekvern\Core\Utils\Utils;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Types\Lootbox;

class VoteShopForm extends MenuForm {

    /**
     * VoteShopForm constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player) {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Vote Shop";
        $text = "Vote points: " . $player->getDataSession()->getVotePoints();
        $options = [];
        $options[] = new MenuOption("3x Ultra Crate Keys (1 Point)");
        $options[] = new MenuOption("Sacred Stone (2 Points)");
        $options[] = new MenuOption("King Kit (2 Points)");
        $options[] = new MenuOption("2x Epic Crate Key (3 Points)");
        $options[] = new MenuOption("2x Legendary Crate Key (5 Points)");
        $options[] = new MenuOption("Holy Box (7 Points)");
        $options[] = new MenuOption("Deity Kit (14 Points)");
        $options[] = new MenuOption("Royalty Lootbox (16 Points)");
        $options[] = new MenuOption("Monthly Crate (50 Points)");
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
        $option = $this->getOption($selectedOption);
        if($player->getInventory()->getSize() === count($player->getInventory()->getContents())) {
            $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Full Inventory", TextFormat::GRAY . "Clear out your inventory!");
            $player->sendMessage(Translation::getMessage("fullInventory"));
            $player->playErrorSound();
            return;
        }
        switch($option->getText()) {
            case "3x Ultra Crate Keys (1 Point)":
                $points = 1;
                $item = (new CrateKeyNote(Crate::ULTRA, 3))->getItemForm();
                break;
            case "Sacred Stone (2 Points)":
                $points = 2;
                $item = (new SacredStone())->getItemForm();
                break;
            case "King Kit (2 Points)":
                $points = 2;
                $item = (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("King")))->getItemForm();
                break;
            case "2x Epic Crate Key (3 Points)":
                $points = 3;
                $item = (new CrateKeyNote(Crate::EPIC, 2))->getItemForm();
                break;
            case "2x Legendary Crate Key (5 Points)":
                $points = 3;
                $item = (new CrateKeyNote(Crate::LEGENDARY, 2))->getItemForm();
                break;
            case "Holy Box (7 Points\)":
                $kits = Nexus::getInstance()->getServerManager()->getKitHandler()->getSacredKits();
                $kit = $kits[array_rand($kits)];
                $points = 7;
                $item = (new HolyBox($kit))->getItemForm();
                break;
            case "Deity Kit (14 Points)":
                $points = 14;
                $item = (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Deity")))->getItemForm();
                break;
            case "Royalty Lootbox (16 Points)":
                    $points = 16;
                    $item = (new Lootbox("Royalty", TextFormat::GOLD . "ROYALTY"))->getItemForm();
                    break;
            case "Monthly Crate (50 Points)":
                $points = 50;
                $item = (new MonthlyCrate())->getItemForm();
                break;
            default:
                return;
        }
        if($player->getDataSession()->getVotePoints() < $points) {
            $player->sendMessage(Translation::getMessage("notEnoughPoints"));
            return;
        }
        $player->getDataSession()->subtractVotePoints($points);
        $player->sendMessage(Translation::getMessage("buy", [
            "amount" => TextFormat::GREEN . "x1",
            "item" => TextFormat::DARK_GREEN . $item->getCustomName(),
            "price" => TextFormat::LIGHT_PURPLE . "$points vote points",
        ]));
        $player->getInventory()->addItem($item);
    }
}