<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Crate\Task;

use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Item\Types\HolyBox;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ChestOpenSound;
use Xekvern\Core\Server\Crate\Crate;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\world\sound\ChestCloseSound;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\ExplodeSound;

class OpenCrateTask extends Task {

    /** @var int */
    private $ticks = 0;

    /** @var InvMenu */
    private $inventory;

    /** @var Crate */
    private $crate;

    /** @var InvMenuInventory */
    private $actualInventory;

    /** @var NexusPlayer */
    private $owner;

    /** @var Reward[] */
    private $rewards;

    /** @var Reward[] */
    private $finalRewards = [];

    /** @var int[] */
    private $rollQueue = [];

    /**
     * OpenCrateTask constructor.
     *
     * @param NexusPlayer $owner
     * @param string $month
     * @param array $rewards
     */
    public function __construct(NexusPlayer $owner, Crate $crate) {
        $this->owner = $owner;
        $this->crate = $crate;
        $this->rewards = $crate->getRewards();
        $position = $crate->getPosition();
        $owner->broadcastSound(new ChestOpenSound(), [$owner]);
        $pk = BlockEventPacket::create(
            new BlockPosition($position->getFloorX(), $position->getFloorY(), $position->getFloorZ()),
            1,
            1
        );
        $owner->getNetworkSession()->sendDataPacket($pk);

        $this->inventory = InvMenu::create(InvMenu::TYPE_CHEST);
        $this->inventory->setListener(InvMenu::readonly());
        $customName = TextFormat::BOLD . TextFormat::YELLOW . $crate->getName() . " Crate";
        $this->inventory->setName($customName);
        $this->actualInventory = $this->inventory->getInventory();
        $chest = VanillaBlocks::CHEST()->asItem();
        $chest->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "???");
        $chest->setLore([
            "",
            TextFormat::RESET . TextFormat::WHITE . "Click to reveal"
        ]);
        $glass = VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::GRAY())->asItem();
        $glass->setCustomName(" ");
        for($i = 0; $i < 27; $i++) {
            if($i >= 12 and $i <= 14) {
                $this->actualInventory->setItem($i, $chest);
                continue;
            }
            $this->actualInventory->setItem($i, $glass);
        }
        $this->inventory->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            $i = $action->getSlot();
            if(isset($this->rollQueue[$action->getSlot()])) {
                return;
            }
            if($i >= 12 and $i <= 14) {  
                $this->rollQueue[$action->getSlot()] = 0;
            }
        }));
        $this->inventory->setInventoryCloseListener(function(Player $player, InvMenuInventory $inventory): void {
            for($i = count($this->finalRewards); $i < 3; $i++) {
                $reward = $this->getReward(0, true);
                $this->finalRewards[] = $reward;
            }
            foreach($this->finalRewards as $reward) {
                $callable = $reward->getCallback();
                $callable($this->owner);
            }
            $player->broadcastSound(new ChestCloseSound(), [$player]);
            $player->broadcastSound(new ExplodeSound(), [$player]);
            $pk = BlockEventPacket::create(
                new BlockPosition($this->crate->getPosition()->getFloorX(), $this->crate->getPosition()->getFloorY(), $this->crate->getPosition()->getFloorZ()),
                1,
                0
            );
            $player->getNetworkSession()->sendDataPacket($pk);
            $this->getHandler()->cancel();
        });
        $this->inventory->send($owner);
    }

    /**
     * @param int $currentTick
     */
    public function onRun(): void {
        if($this->owner === null or $this->owner->isOnline() === false) {
            $this->getHandler()->cancel();
            return;
        }
        foreach($this->rollQueue as $slot => $tick) {
            $this->rollQueue[$slot]++;
            if($tick <= 20 and $tick % 4 == 0) {
                $this->randomize($slot);
                continue;
            }
            if($tick <= 40 and $tick % 7 == 0) {
                $this->randomize($slot);
                continue;
            }
            if($tick <= 60 and $tick % 10 == 0) {
                $this->randomize($slot);
                continue;
            }
            if($tick <= 80 and $tick % 13 == 0) {
                $this->randomize($slot);
                continue;
            }
            if($tick === 100) {
                $this->finalRewards[] = $this->randomize($slot, true);
                continue;
            }
        }
        if(count($this->finalRewards) === 3) {
            $this->ticks++;
        }
        else {
            if(count($this->finalRewards) > 3) {
                $this->finalRewards = array_slice($this->finalRewards, 0, 3, true);
            }
        }
        if($this->ticks === 60) {
            if(count($this->finalRewards) === 3) {
                $this->owner->playXpLevelUpSound();
                $this->owner->removeCurrentWindow($this->actualInventory, true);
            }
        }
    }

    /**
     * @param int $slot
     * @param bool $final
     *
     * @return Reward|Item
     */
    public function randomize(int $slot, bool $final = false) {
        $reward = $this->getReward(0, $final);
        $this->actualInventory->setItem($slot, $reward->getItem());
        $this->owner->broadcastSound(new ClickSound(), [$this->owner]);
        return $reward;
    }

    /**
     * @param int $loop
     * @param bool $final
     *
     * @return Reward
     */
    public function getReward(int $loop = 0, bool $final = false): Reward {
        $chance = mt_rand(0, 100);
        $index = array_rand($this->rewards);
        $reward = $this->rewards[$index];
        if($loop >= 10) {
            if($final) {
                unset($this->rewards[$index]);
            }
            return $reward;
        }
        if($reward->getChance() <= $chance) {
            if($final) {
                unset($this->rewards[$index]);
            }
            return $this->getReward($loop + 1, $final);
        }
        if($final) {
            unset($this->rewards[$index]);
        }
        return $reward;
    }
}