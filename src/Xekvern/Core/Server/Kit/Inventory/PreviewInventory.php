<?php

namespace Xekvern\Core\Server\Kit\Inventory;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\ItemFactory;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Kit\Forms\KitPreviewForm;
use Xekvern\Core\Server\Kit\Kit;

class PreviewInventory
{
    /** @var NexusPlayer */
    private $owner;

    /** @var InvMenu */
    private $menu;

    public function __construct(NexusPlayer $owner, Kit $kit) {
        $this->owner = $owner;
        $this->menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $this->menu->setName($kit->getColoredName() . TextFormat::RESET . " Preview");
        $home = VanillaBlocks::OAK_DOOR()->asItem();
        $home->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "Home");
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Return to the main";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "kit menu.";
        $home->setLore($lore);
        $glass = VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::GRAY())->asItem();
        $glass->setCustomName(" ");
        for($i = 45; $i < 54; $i++) {
            $this->menu->getInventory()->setItem($i, $glass);
        }
        $this->menu->getInventory()->setItem(45, $home);
        foreach($kit->getItems() as $item) {
            $item->getNamedTag()->setString("AntiDupe", "AntiDupe");
            $this->menu->getInventory()->addItem($item);
        }
        $this->menu->setListener(InvMenu::readonly(function(InvMenuTransaction $transaction) use ($kit) {
            $player = $transaction->getPlayer();
            $action = $transaction->getAction();
            $in = $action->getSourceItem();
            $out = $action->getTargetItem();
            if($in->getCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "Home")) {
                $player->removeCurrentWindow();
                Nexus::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($kit, $player): void {
                    $player->sendForm(new KitPreviewForm($player, $kit));
                }), 20);
            }
            return false;
        }));
    }

    public function send(): void {
        $this->menu->send($this->owner);
    }
}