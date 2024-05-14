<?php

namespace Xekvern\Core\Server\Kit\Inventory;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Kit\Forms\KitPreviewForm;
use Xekvern\Core\Server\Kit\Kit;
use Xekvern\Core\Server\Kit\SacredKit;

class KitListInventory
{
    /** @var NexusPlayer */
    private $owner;

    /** @var InvMenu */
    private $menu;

    public function __construct(NexusPlayer $owner, string $type, array $kits) {
        $this->owner = $owner;
        $this->menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $this->menu->setName(TextFormat::BOLD . TextFormat::AQUA . $type);
        $glass = VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::GRAY())->asItem();
        $glass->setCustomName(" ");
        for($i = 0; $i < 27; $i++) {
            $kit = array_shift($kits);
            if($kit instanceof Kit) {
                $chest = VanillaBlocks::CHEST()->asItem();
                $chest->setCustomName(TextFormat::RESET . TextFormat::YELLOW . $kit->getName() . " Kit");
                $chest->getNamedTag()->setString("kitname", $kit->getName());
                if(!$owner->hasPermission("permission." . strtolower($kit->getName()))) {
                    $lore = [];
                    $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "LOCKED";
                    $chest->setLore($lore);
                    $this->menu->getInventory()->setItem($i, $chest);
                    continue;
                } else {
                    if($kit instanceof SacredKit) {
                        $tier = $owner->getDataSession()->getSacredKitTier($kit);
                        $maxtier = $kit->getMaxTier();
                        $lore = [];
                        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "UNLOCKED";
                        $lore[] = TextFormat::RESET . TextFormat::AQUA . "(Tier " . ItemHandler::getRomanNumber($tier) . "/" . ItemHandler::getRomanNumber($maxtier) . ")";
                        $chest->setLore($lore);
                        $this->menu->getInventory()->setItem($i, $chest);
                        continue;
                    }
                    $lore = [];
                    $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "UNLOCKED";
                    $chest->setLore($lore);
                    $this->menu->getInventory()->setItem($i, $chest);
                    continue;
                }
            } 
        }
        $this->menu->setListener(InvMenu::readonly(function(InvMenuTransaction $transaction) use ($kit) {
            $player = $transaction->getPlayer();
            $action = $transaction->getAction();
            $in = $action->getSourceItem();
            $out = $action->getTargetItem();
            if($in->getTypeId() === VanillaBlocks::CHEST()->asItem()->getTypeId()) {
                $tag = $in->getNamedTag();
                $type = $tag->getString("kitname");
                $kitManager = Nexus::getInstance()->getServerManager()->getKitHandler();
                $kit = $kitManager->getKitByName($type);
                $player->removeCurrentWindow();
                Nexus::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $kit): void {
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