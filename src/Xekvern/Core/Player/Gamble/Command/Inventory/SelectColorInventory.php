<?php

namespace Xekvern\Core\Player\Gamble\Command\Inventory;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Gamble\CoinFlipEntry;
use Xekvern\Core\Player\Gamble\Task\RollCoinFlipTask;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;

class SelectColorInventory extends InvMenu {

    /** @var DyeColor[] */
    private $colors;

    /** @var int */
    private $amount;

    /** @var null|CoinFlipEntry */
    private $target;

    /**
     * SelectColorInventory constructor.
     *
     * @param int $amount
     * @param CoinFlipEntry|null $target
     */
    public function __construct(int $amount, ?CoinFlipEntry $target = null) {
        parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenu::TYPE_CHEST));
        $this->amount = $amount;
        $this->target = $target;
        $this->colors = [
            DyeColor::RED(),
            DyeColor::ORANGE(),
            DyeColor::YELLOW(),
            DyeColor::GREEN(),
            DyeColor::LIGHT_BLUE(),
            DyeColor::PURPLE(),
            DyeColor::GRAY(),
            DyeColor::BLACK(),
            DyeColor::WHITE()
        ];
        $this->initItems();
        $this->setName(TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Select a color");
        $this->setListener(self::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $slot = $transaction->getAction()->getSlot();
            $player = $transaction->getPlayer();
            if(!$player instanceof NexusPlayer) {
                return;
            }
            if($slot < 9) {
                if(isset($this->colors[$slot])) {
                    $color = $this->colorToTextFormat($this->colors[$slot]);
                }
                if($this->target === null) {
                    $player->sendMessage(Translation::getMessage("addCoinFlip"));
                    $player->removeCurrentWindow();
                    $player->getDataSession()->subtractFromBalance($this->amount);
                    Nexus::getInstance()->getPlayerManager()->getGambleHandler()->addCoinFlip($player, new CoinFlipEntry($player, $this->amount, $color, CoinFlipEntry::MONEY));
                }
                else {
                    $target = $this->target->getOwner();
                    if((!$target instanceof NexusPlayer) or (!$target->isOnline()) or (!$target->isLoaded())) {
                        $player->removeCurrentWindow();
                        $player->sendMessage(Translation::getMessage("invalidPlayer"));
                        return;
                    }
                    if($target->getUniqueId()->toString() === $player->getUniqueId()->toString()) {
                        $player->removeCurrentWindow();
                        $player->sendMessage(Translation::getMessage("invalidPlayer"));
                        return;
                    }
                    $cf = $player->getCore()->getPlayerManager()->getGambleHandler()->getCoinFlip($target);
                    if($cf === null) {
                        $player->sendMessage(Translation::getMessage("invalidPlayer"));
                        return;
                    }
                    if($color === $this->target->getColor()) {
                        $player->playErrorSound();
                        return;
                    }
                    $player->getDataSession()->subtractFromBalance($this->amount);
                    $player->removeCurrentWindow();
                    Nexus::getInstance()->getPlayerManager()->getGambleHandler()->removeCoinFlip($target);
                    $task = new RollCoinFlipTask($this->target, new CoinFlipEntry($player, $this->amount, $color, CoinFlipEntry::MONEY));
                    Nexus::getInstance()->getPlayerManager()->getGambleHandler()->addActiveCoinFlip($task);
                    Nexus::getInstance()->getScheduler()->scheduleRepeatingTask($task, 1);
                }
            }
            return;
        }));
    }

    public function initItems(): void {
        for($i = 0; $i < 9; $i++) {
            $color = $this->colors[$i];
            $wool = VanillaBlocks::WOOL()->setColor($color)->asItem();
            if($this->target === null or ($this->target !== null and $this->colorToTextFormat($color) !== $this->target->getColor())) {
                $wool->setCustomName(TextFormat::RESET . TextFormat::BOLD . $this->colorToTextFormat($color) . $this->colorToName($color));
                $lore = [];
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "Click to select this color";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "and start the coin flip for";
                $lore[] = TextFormat::RESET . TextFormat::GREEN . "$" . number_format($this->amount);
                $wool->setLore($lore);
            }
            else {
                $wool->setCustomName(TextFormat::RESET . TextFormat::BOLD . $this->colorToTextFormat($color) . "Opponent's Color");
                $lore = [];
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "This color is not available.";
                $wool->setLore($lore);
            }
            $this->getInventory()->setItem($i, $wool);
        }
    }

    /**
     * @param DyeColor $color
     *
     * @return string
     */
    public function colorToTextFormat(DyeColor $color): string {
        switch($color) {
            case DyeColor::RED():
                return TextFormat::RED;
                break;
            case DyeColor::ORANGE():
                return TextFormat::GOLD;
                break;
            case DyeColor::YELLOW():
                return TextFormat::YELLOW;
                break;
            case DyeColor::GREEN():
                return TextFormat::GREEN;
                break;
            case DyeColor::LIGHT_BLUE():
                return TextFormat::AQUA;
                break;
            case DyeColor::PURPLE():
                return TextFormat::DARK_PURPLE;
                break;
            case DyeColor::GRAY():
                return TextFormat::GRAY;
                break;
            case DyeColor::BLACK():
                return TextFormat::BLACK;
                break;
            default:
                return TextFormat::WHITE;
                break;
        }
    }

    /**
     * @param DyeColor $color
     *
     * @return string
     */
    public function colorToName(DyeColor $color): string {
        switch($color) {
            case DyeColor::RED():
                return "Red";
                break;
            case DyeColor::ORANGE():
                return "Orange";
                break;
            case DyeColor::YELLOW():
                return "Yellow";
                break;
            case DyeColor::GREEN():
                return "Green";
                break;
            case DyeColor::LIGHT_BLUE():
                return "Blue";
                break;
            case DyeColor::PURPLE():
                return "Purple";
                break;
            case DyeColor::GRAY():
                return "Gray";
                break;
            case DyeColor::BLACK():
                return "Black";
                break;
            default:
                return "White";
                break;
        }
    }
}