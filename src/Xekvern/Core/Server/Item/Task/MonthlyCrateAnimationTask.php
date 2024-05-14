<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Task;

use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Server\Item\Types\HolyBox;

class MonthlyCrateAnimationTask extends Task {

    /** @var int */
    private $ticks = 0;

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

    /** @var null|Item */
    private $bonusReward = null;

    /** @var int[] */
    private $rollQueue = [];

    /**
     * MonthlyCrateAnimationTask constructor.
     *
     * @param NexusPlayer $owner
     * @param string $month
     * @param array $rewards
     */
    public function __construct(NexusPlayer $owner, string $month, array $rewards) {
        $this->owner = $owner;
        $this->rewards = $rewards;
        $this->inventory = InvMenu::create(InvMenu::TYPE_HOPPER);
        $this->inventory->setListener(InvMenu::readonly());
        $customName = TextFormat::OBFUSCATED . TextFormat::BOLD . TextFormat::RED . "|" . TextFormat::GOLD . "|" . TextFormat::YELLOW . "|" . TextFormat::GREEN . "|" . TextFormat::AQUA . "|" . TextFormat::LIGHT_PURPLE . "|" . TextFormat::RESET . TextFormat::WHITE . TextFormat::BOLD . " $month Crate";
        $this->inventory->setName($customName);
        $this->actualInventory = $this->inventory->getInventory();
        $chest = VanillaBlocks::CHEST()->asItem();
        $chest->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "???");
        $chest->setLore([
            "",
            TextFormat::RESET . TextFormat::WHITE . "Click to reveal"
        ]);
        for($i = 0; $i <= 3; $i++) {
            $this->actualInventory->setItem($i, $chest);
        }
        $chest = VanillaBlocks::ENDER_CHEST()->asItem();
        $chest->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "???");
        $chest->setLore([
            "",
            TextFormat::RESET . TextFormat::WHITE . "Click to reveal"
        ]);
        $this->actualInventory->setItem(4, $chest);
        $this->inventory->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            if(isset($this->rollQueue[$action->getSlot()])) {
                return;
            }
            $this->rollQueue[$action->getSlot()] = 0;
        }));
        $this->inventory->setInventoryCloseListener(function(Player $player, InvMenuInventory $inventory): void {
            for($i = count($this->finalRewards); $i < 4; $i++) {
                $reward = $this->getReward(0, true);
                $this->finalRewards[] = $reward;
            }
            foreach($this->finalRewards as $reward) {
                $callable = $reward->getCallback();
                $callable($this->owner);
            }
            if($this->bonusReward === null) {
                $kits = Nexus::getInstance()->getServerManager()->getKitHandler()->getSacredKits();
                $kit = $kits[array_rand($kits)];
                $this->bonusReward = (new HolyBox($kit))->getItemForm();
            }
            $player->getInventory()->addItem($this->bonusReward);
            Server::getInstance()->broadcastMessage(TextFormat::WHITE . $this->owner->getName() . TextFormat::GRAY . " has opened a " . $this->inventory->getName() . TextFormat::RESET . TextFormat::GRAY . " and received:");
            foreach($this->finalRewards as $reward) {
                $item = $reward->getItem();
                $name = $item->getName();
                if($item->hasCustomName()) {
                    $name = $item->getCustomName();
                }
                Server::getInstance()->broadcastMessage(TextFormat::GRAY . TextFormat::BOLD . " * " . TextFormat::RESET . $name);
            }
            $name = $this->bonusReward->getName();
            if($this->bonusReward->hasCustomName()) {
                $name = $this->bonusReward->getCustomName();
            }
            Server::getInstance()->broadcastMessage(TextFormat::GRAY . TextFormat::BOLD . " * " . TextFormat::RESET . $name);
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
                if($slot === 4) {
                    $this->bonusReward = $this->randomize($slot);
                    continue;
                }
                $this->finalRewards[] = $this->randomize($slot, true);
                continue;
            }
        }
        if(count($this->finalRewards) === 4 and $this->bonusReward !== null) {
            $this->ticks++;
        }
        else {
            if(count($this->finalRewards) > 4) {
                $this->finalRewards = array_slice($this->finalRewards, 0, 4, true);
            }
        }
        if($this->ticks === 60) {
            if(count($this->finalRewards) === 4 and $this->bonusReward !== null) {
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
        if($slot !== 4) {
            $reward = $this->getReward(0, $final);
            $this->actualInventory->setItem($slot, $reward->getItem());
        }
        else {
            $kits = Nexus::getInstance()->getServerManager()->getKitHandler()->getSacredKits();
            $kit = $kits[array_rand($kits)];
            $reward = (new HolyBox($kit))->getItemForm();
            $this->actualInventory->setItem($slot, $reward);
        }
        $this->owner->playNoteSound();
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