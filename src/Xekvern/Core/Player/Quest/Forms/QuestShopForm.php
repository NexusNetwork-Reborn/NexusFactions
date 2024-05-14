<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Quest\Forms;

use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\CrateKeyNote;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Types\EnchantmentCrystal;
use Xekvern\Core\Server\Item\Types\EnchantmentScroll;
use Xekvern\Core\Server\Item\Types\GeneratorsBag;
use Xekvern\Core\Server\Item\Types\HolyBox;
use Xekvern\Core\Server\Item\Types\MoneyNote;
use Xekvern\Core\Server\Item\Types\SacredStone;
use Xekvern\Core\Server\Item\Types\XPNote;
use Xekvern\Core\Translation\Translation;

class QuestShopForm extends MenuForm {

    /**
     * QuestShopForm constructor.
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player) {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Quest Shop";
        $text = "Quest points: " . $player->getDataSession()->getQuestPoints();
        $options = [];
        $options[] = new MenuOption("$10,000 (1 Point)");
        $options[] = new MenuOption("1,500 XP Note (2 Points)");
        $options[] = new MenuOption("x2 Ultra Crate Keys (3 Points)");
        $options[] = new MenuOption("Enchantment Scroll (5 Points)");
        $options[] = new MenuOption("Random Enchantment Crystal (8 Points)");
        $options[] = new MenuOption("Random Enchantment Book (10 Points)");
        $options[] = new MenuOption("Generators Bag (15 Points)");
        $options[] = new MenuOption("Sacred Stone (30 Points)");
        $options[] = new MenuOption("Holy Box (100 Points)");
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     * @throws TranslationException
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $option = $this->getOption($selectedOption);
        if ($player->getInventory()->getSize() === count($player->getInventory()->getContents())) {
            $player->sendMessage(Translation::getMessage("fullInventory"));
            return;
        }
        switch ($option->getText()) {
            case "$10,000 (1 Point)":
                $points = 1;
                $item = (new MoneyNote(10000))->getItemForm();
                break;
            case "1,500 XP Note (2 Points)":
                $points = 2;
                $item = (new XPNote(1500))->getItemForm();
                break;
            case "x2 Ultra Crate Keys (3 Points)":
                $points = 3;
                $item = (new CrateKeyNote(Crate::ULTRA, 2))->getItemForm();
                break;
            case "Enchantment Scroll (5 Points)":
                $points = 5;
                $item = (new EnchantmentScroll())->getItemForm();
                break;
            case "Random Enchantment Crystal (8 Points)":
                $points = 8;
                $item = (new EnchantmentCrystal(ItemHandler::getRandomEnchantment()))->getItemForm();
                break;
            case "Random Enchantment Book (10 Points)":
                $points = 10;
                $item = (new EnchantmentBook(ItemHandler::getRandomEnchantment(), 100))->getItemForm();
                break;
            case "Generators Bag (15 Points)":
                $points = 15;
                $item = (new GeneratorsBag())->getItemForm();
                break;
            case "Sacred Stone (30 Points)":
                $points = 30;
                $item = (new SacredStone())->getItemForm();
                break;
            case "Holy Box (100 Points)":
                $kits = Nexus::getInstance()->getServerManager()->getKitHandler()->getSacredKits();
                $kit = $kits[array_rand($kits)];
                $points = 150;
                $item = (new HolyBox($kit))->getItemForm();
                break;
            default:
                return;
        }
        if ($player->getDataSession()->getQuestPoints() < $points) {
            $player->sendMessage(Translation::getMessage("notEnoughPoints"));
            return;
        }
        $player->getDataSession()->subtractQuestPoints($points);
        $player->sendMessage(Translation::getMessage("buy", ["amount" => TextFormat::GREEN . "1", "item" => TextFormat::DARK_GREEN . $item->getCustomName(), "price" => TextFormat::LIGHT_PURPLE . "$points quest points",]));
        $player->getInventory()->addItem($item);
    }
}