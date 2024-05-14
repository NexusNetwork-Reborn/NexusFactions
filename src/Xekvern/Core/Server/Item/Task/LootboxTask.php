<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Task;

use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Player\NexusPlayer;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class LootboxTask extends Task {

    /** @var InvMenu */
    private $inventory;

    /** @var InvMenuInventory */
    private $actualInventory;

    /** @var NexusPlayer */
    private $owner;

    /** @var Reward[] */
    private $rewards;

    /** @var Reward[] */
    private $finalRewards = [];

    /** @var int */
    private $duration = 60;

    private const INSIDE = [11, 12, 13, 14, 15];

    /**
     * LootboxTask constructor.
     *
     * @param NexusPlayer $owner
     * @param string $month
     * @param array $rewards
     */
    public function __construct(NexusPlayer $owner, string $customName, array $rewards) {
        $this->owner = $owner;
        $this->rewards = $rewards;
        $this->inventory = InvMenu::create(InvMenu::TYPE_CHEST);
        $this->inventory->setListener(InvMenu::readonly());
        $this->inventory->setName($customName);
        $this->actualInventory = $this->inventory->getInventory();
        $chest = VanillaBlocks::CHEST()->asItem();
        $chest->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "???");
        $glass = VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::GRAY())->asItem();
        $glass->setCustomName(" ");
        for($i = 0; $i <= 26; $i++) {
            $this->actualInventory->setItem($i, $glass);
        }
        foreach(self::INSIDE as $slot) {
            $this->actualInventory->setItem($slot, $chest);
        }
        $this->inventory->setInventoryCloseListener(function(Player $player, InvMenuInventory $inventory): void {
            for($i = count($this->finalRewards); $i < 5; $i++) {
                $reward = $this->getReward(0, true);
                $this->finalRewards[] = $reward;
            }
            foreach($this->finalRewards as $reward) {
                $callable = $reward->getCallback();
                $callable($this->owner);
            }
            Server::getInstance()->broadcastMessage(TextFormat::WHITE . $this->owner->getName() . TextFormat::GRAY . " has opened a " . $this->inventory->getName() . TextFormat::RESET . TextFormat::GRAY . " and received:");
            foreach($this->finalRewards as $reward) {
                $item = $reward->getItem();
                $name = $item->getName();
                if($item->hasCustomName()) {
                    $name = $item->getCustomName();
                }
                Server::getInstance()->broadcastMessage(TextFormat::GRAY . TextFormat::BOLD . " * " . TextFormat::RESET . $name);
            }
            $this->getHandler()->cancel();
        });
        $this->inventory->send($owner);
    }

    /**
     * @param int $currentTick
     */
    public function onRun(): void {
        --$this->duration;
        if($this->owner === null or $this->owner->isOnline() === false) {
            $this->getHandler()->cancel();
            return;
        }
        if($this->duration >= 3 && $this->duration <= 60 && $this->duration > 0) {
            foreach(self::INSIDE as $slot) {
				$this->randomize($slot);
			}
        }
        if($this->duration === 2) {
            foreach(self::INSIDE as $slot) {
				$this->finalRewards[] = $this->randomize($slot, true);
			}
        }
        if($this->duration === 1) {
            $this->finalRewards = array_slice($this->finalRewards, 0, 5, true);
        }
        if($this->duration <= 0) {
            $this->owner->playXpLevelUpSound();
            $this->owner->removeCurrentWindow($this->actualInventory, true);
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
        $this->owner->playDingSound();
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