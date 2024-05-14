<?php

namespace Xekvern\Core\Player\Gamble\Task;

use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ClickSound;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Gamble\CoinFlipEntry;
use Xekvern\Core\Player\Gamble\Event\CoinFlipLoseEvent;
use Xekvern\Core\Player\Gamble\Event\CoinFlipWinEvent;
use Xekvern\Core\Player\NexusPlayer;

class RollCoinFlipTask extends Task {

    /** @var CoinFlipEntry */
    private $ownerEntry;

    /** @var CoinFlipEntry */
    private $targetEntry;

    /** @var bool */
    private $chosen = false;

    /** @var null|CoinFlipEntry */
    private $winner = null;

    /** @var null|CoinFlipEntry */
    private $loser;

    /** @var InvMenu */
    private $inventory;

    /** @var InvMenuInventory */
    private $actualInventory;

    /** @var int */
    private $ticks = 0;

    /** @var int */
    private $countDown = 100;

    /** @var int */
    private $delay = 5;

    /** @var int */
    private $rolls = 0;

    /**
     * RollCoinFlipTask constructor.
     *
     * @param CoinFlipEntry $ownerEntry
     * @param CoinFlipEntry $targetEntry
     */
    public function __construct(CoinFlipEntry $ownerEntry, CoinFlipEntry $targetEntry) {
        $this->ownerEntry = $ownerEntry;
        $this->targetEntry = $targetEntry;
        $this->inventory = InvMenu::create(InvMenu::TYPE_HOPPER);
        $this->inventory->setListener(InvMenu::readonly());
        $this->inventory->setName(TextFormat::BOLD . TextFormat::YELLOW . "Rolling...");
        $this->actualInventory = $this->inventory->getInventory();
        $ownerEntry->getOwner()->sendDelayedWindow($this->inventory);
        $targetEntry->getOwner()->sendDelayedWindow($this->inventory);
    }

    /**
     * @param int $currentTick
     */
    public function onRun(): void {
        $owner = $this->ownerEntry->getOwner();
        $target = $this->targetEntry->getOwner();
        if($owner === null or $owner->isOnline() === false or $target === null or $target->isOnline() === false) {
            if($this->getHandler() !== null) {
                $this->getHandler()->cancel();
            }
            return;
        }
        if($this->delay-- > 0) {
            return;
        }
        if($this->countDown > 0) {
            if($this->countDown % 20 == 0) {
                $count = floor($this->countDown / 20);
                $glass = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GRAY())->asItem();
                $glass->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Starting in $count" . "s");
                $this->actualInventory->setItem(2, $glass);
                $owner = $this->ownerEntry->getOwner();
                $owner->getWorld()->addSound($owner->getPosition(), new ClickSound(), [$owner]);
                $owner = $this->targetEntry->getOwner();
                $owner->getWorld()->addSound($owner->getPosition(), new ClickSound(), [$owner]);
            }
            $this->countDown--;
            return;
        }
        $this->ticks++;
        if(!$this->chosen) {
            if($this->ticks < 40 and $this->ticks % 2 == 0) {
                $this->roll();
                return;
            }
            if($this->ticks < 80 and $this->ticks % 5 == 0) {
                $this->roll();
                return;
            }
            if($this->ticks < 140 and $this->ticks % 7 == 0) {
                $this->roll();
                return;
            }
            if($this->ticks < 180 and $this->ticks % 13 == 0) {
                $this->roll();
                return;
            }
            if($this->ticks === 220) {
                if(!$this->chosen) {
                    $this->finalRoll();
                }
                return;
            }
        }
        if($this->ticks === 300) {
            $owner->removeCurrentWindow();
            $target->removeCurrentWindow();
        }
    }

    public function roll(): void {
        $this->rolls++;
        $entries = [
            $this->ownerEntry,
            $this->targetEntry
        ];
        /** @var CoinFlipEntry $entry */
        $entry = $entries[$this->rolls % 2];
        $color = $entry->getColor();
        $item = $this->colorToItem($color);
        $item->setCustomName(TextFormat::RESET . $color . TextFormat::BOLD . $entry->getOwner()->getName());
        $this->actualInventory->setItem(2, $item);
        $this->ownerEntry->getOwner()->playNoteSound(floor($this->rolls / 2));
        $this->targetEntry->getOwner()->playNoteSound(floor($this->rolls / 2));
        foreach($this->actualInventory->getViewers() as $viewer) {
            if($viewer instanceof NexusPlayer and $viewer->isOnline()) {
                $viewer->getNetworkSession()->getInvManager()->syncContents($this->actualInventory);
            }
        }
    }

    public function finalRoll(): void {
        $this->chosen = true;
        Nexus::getInstance()->getPlayerManager()->getGambleHandler()->removeActiveCoinFlip($this);
        if(50 >= mt_rand(1, 100)) {
            $this->winner = $this->ownerEntry;
            $this->loser = $this->targetEntry;
        }
        else {
            $this->winner = $this->targetEntry;
            $this->loser = $this->ownerEntry;
        }
        $color = $this->winner->getColor();
        $item = $this->colorToItem($color);
        $item->setCustomName(TextFormat::RESET . $color . TextFormat::BOLD . $this->winner->getOwner()->getName());
        $item->setLore([TextFormat::RESET . $color . TextFormat::BOLD . $this->winner->getOwner()->getName() . TextFormat::RESET . TextFormat::GRAY . " has won the Coin Flip!"]);
        $this->actualInventory->setItem(2, $item);
        foreach($this->actualInventory->getViewers() as $viewer) {
            if($viewer instanceof NexusPlayer and $viewer->isOnline()) {
                $viewer->getNetworkSession()->getInvManager()->syncContents($this->actualInventory);
            }
        }
        $winner = $this->winner->getOwner();
        $loser = $this->loser->getOwner();
        $winner->playConsecutiveDingSound();
        $loser->playConsecutiveDingSound();
        $amount = $this->getOwnerEntry()->getAmount();
        $gambleHandler = Nexus::getInstance()->getPlayerManager()->getGambleHandler();
        $gambleHandler->addWin($winner);
        $gambleHandler->addLoss($loser);
        $gambleHandler->getRecord($winner, $wins, $losses);
        $gambleHandler->getRecord($loser, $wins2, $losses2);
        $ev = new CoinFlipWinEvent($this->winner->getOwner(), $amount);
        $ev->call();
        $ev = new CoinFlipLoseEvent($this->loser->getOwner(), $amount);
        $ev->call();
        $winTotal = $amount * 2;
        $loserColor = $this->loser->getColor();
        Server::getInstance()->broadcastMessage(TextFormat::RESET . $color . "■ " . TextFormat::GREEN . $winner->getName() . TextFormat::GRAY . " ($wins-$losses) has defeated " . $loserColor . "■ " . TextFormat::RED . $loser->getName() . TextFormat::GRAY . " ($wins2-$losses2) in a $" . number_format($winTotal) . " /coinflip");
        $winner->getDataSession()->addToBalance($winTotal);
    }

    /**
     * @param string $color
     *
     * @return Item
     */
    public function colorToItem(string $color): Item {
        switch($color) {
            case TextFormat::RED:
                return VanillaBlocks::WOOL()->setColor(DyeColor::RED)->asItem();
                break;
            case TextFormat::GOLD:
                return VanillaBlocks::WOOL()->setColor(DyeColor::ORANGE())->asItem();
                break;
            case TextFormat::YELLOW:
                return VanillaBlocks::WOOL()->setColor(DyeColor::YELLOW())->asItem();
                break;
            case TextFormat::GREEN:
                return VanillaBlocks::WOOL()->setColor(DyeColor::GREEN())->asItem();
                break;
            case TextFormat::AQUA:
                return VanillaBlocks::WOOL()->setColor(DyeColor::LIGHT_BLUE())->asItem();
                break;
            case TextFormat::DARK_PURPLE:
                return VanillaBlocks::WOOL()->setColor(DyeColor::PURPLE())->asItem();
                break;
            case TextFormat::GRAY:
                return VanillaBlocks::WOOL()->setColor(DyeColor::GRAY())->asItem();
                break;
            case TextFormat::BLACK:
                return VanillaBlocks::WOOL()->setColor(DyeColor::BLACK())->asItem();
                break;
            default:
                return VanillaBlocks::WOOL()->setColor(DyeColor::WHITE())->asItem();
                break;
        }
    }

    /**
     * @return CoinFlipEntry
     */
    public function getOwnerEntry(): CoinFlipEntry {
        return $this->ownerEntry;
    }

    /**
     * @return CoinFlipEntry
     */
    public function getTargetEntry(): CoinFlipEntry {
        return $this->targetEntry;
    }
}