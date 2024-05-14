<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Kit\Forms;

use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\scheduler\ClosureTask;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\Types\ChestKit;
use Xekvern\Core\Server\Kit\Inventory\KitInventory;
use Xekvern\Core\Server\Kit\Inventory\PreviewInventory;
use Xekvern\Core\Server\Kit\Kit;
use Xekvern\Core\Server\Kit\SacredKit;
use Xekvern\Core\Translation\Translation;

class KitPreviewForm extends MenuForm
{

    /** @var NexusPlayer */
    private $player;

    /** @var Kit */
    private $kit;

    /**
     * KitPreviewForm constructor.
     */
    public function __construct(NexusPlayer $player, Kit $kit)
    {
        $this->kit = $kit;
        $title = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . $kit->getName() . " Kit";
        $text = "What would you like to do?";
        $options = [];
        $lowercaseName = strtolower($kit->getName());
        if ($player->hasPermission("permission.$lowercaseName")) {
            $time = time();
            $cooldown = $player->getDataSession()->getKitCooldown($kit);
            $cooldown = $kit->getCooldown() - (time() - $cooldown);
            if($cooldown > 0) {
                $days = floor($cooldown / 86400);
                $hours = (int)($cooldown / 3600) % 24;
                $minutes = ((int)($cooldown / 60) % 60);
                $seconds = $cooldown % 60;
                $time = $days. "d $hours" . "h $minutes" . "m $seconds" . "s";
                $options[] = new MenuOption("Equip Kit\n" . TextFormat::RED . "CD: " . $time);
            } else $options[] = new MenuOption("Equip Kit\n" . TextFormat::BOLD . TextFormat::GREEN . "UNLOCKED");
        } else $options[] = new MenuOption("Equip Kit\n" . TextFormat::BOLD . TextFormat::RED . "LOCKED");
        $options[] = new MenuOption("Preview Kit");
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        $option = $this->getOption($selectedOption);
        $text = $option->getText();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $time = time();
        $kitManager = Nexus::getInstance()->getServerManager()->getKitHandler();
        $kit = $kitManager->getKitByName($this->kit->getName());
        if ($text === "Preview Kit" and $player instanceof NexusPlayer) {
            if ($player->getNetworkSession()->getPing() > 499) {
                $player->sendMessage(Translation::RED . "You cannot use this while you have a high ping.");
                return;
            };
            Nexus::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($kit, $player): void {
                (new PreviewInventory($player, $kit))->send();
            }), 20);
            return;
        } else {
            $name = $this->kit->getName();
            $lowercaseName = strtolower($this->kit->getName());
            if (!$player->hasPermission("permission.$lowercaseName")) {
                $player->sendMessage(Translation::getMessage("noPermission"));
                return;
            }
            $cooldown = $player->getDataSession()->getKitCooldown($kit);
            $cooldown = $kit->getCooldown() - ($time - $cooldown);
            if($cooldown > 0) {
                $days = floor($cooldown / 86400);
                $hours = $hours = (int)($cooldown / 3600) % 24;
                $minutes = ((int)($cooldown / 60) % 60);
                $seconds = $cooldown % 60;
                $time = $days. "d $hours" . "h $minutes" . "m $seconds" . "s";
                $player->sendMessage(Translation::getMessage("kitCooldown", [
                    "time" => TextFormat::RED . $time
                ]));
                return;
            }
            $tier = 1;
            if($kit instanceof SacredKit) {
                $tier = $player->getDataSession()->getSacredKitTier($kit);
            }
            $item = (new ChestKit($player->getCore()->getServerManager()->getKitHandler()->getKitByName($name), $tier))->getItemForm();
            if(!$player->getInventory()->canAddItem($item)) {
                $player->sendMessage(Translation::getMessage("fullInventory"));
                return;
            }
            $player->getInventory()->addItem($item);
            $player->sendTitle(TextFormat::GREEN . TextFormat::BOLD . "Equipped", TextFormat::GRAY . $name . " Kit");
            $player->getDataSession()->setKitCooldown($kit);
        }
    }
}
