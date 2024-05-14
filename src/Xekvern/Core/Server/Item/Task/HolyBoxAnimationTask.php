<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Task;

use Xekvern\Core\Server\Kit\SacredKit;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Server\Item\Types\ChestKit;
use Xekvern\Core\Server\Item\Types\HolyBox;

class HolyBoxAnimationTask extends Task
{

    /** @var int */
    private $ticks = 0;

    /** @var InvMenu */
    private $inventory;

    /** @var InvMenuInventory */
    private $actualInventory;

    /** @var NexusPlayer */
    private $owner;

    /** @var SacredKit */
    private $kit;

    /** @var Reward[] */
    private $rewards;

    /** @var Reward[] */
    private $selector = [];

    /**
     * HolyBoxAnimationTask constructor.
     *
     * @param NexusPlayer $owner
     * @param SacredKit $kit
     */
    public function __construct(NexusPlayer $owner, SacredKit $kit)
    {
        $this->owner = $owner;
        $this->kit = $kit;
        $this->rewards = [
            new Reward("Permanent Access", VanillaBlocks::WOOL()->setColor(DyeColor::GREEN())->asItem()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "Permanent Access"), function (NexusPlayer $player) {
                if (!$player->hasPermission("permission." . strtolower($this->kit->getName()))) {
                    $player->getServer()->broadcastMessage(TextFormat::YELLOW . $player->getName() . TextFormat::AQUA . " has unlocked the " . $this->kit->getColoredName() . TextFormat::RESET . TextFormat::AQUA . " Kit at tier 1!");
                    $player->getDataSession()->addPermission("permission." . strtolower($this->kit->getName()));
                } else {
                    $tier = $player->getDataSession()->getSacredKitTier($this->kit);
                    if ($tier >= $this->kit->getMaxTier()) {
                        $player->sendMessage(TextFormat::BOLD . TextFormat::RED . "You've reached the max tier! Your holy box was returned to you!");
                        $item = (new HolyBox($this->kit))->getItemForm();
                        if ($player->getInventory()->canAddItem($item)) {
                            $player->getInventory()->addItem($item);
                            return;
                        }
                    }
                    ++$tier;
                    $player->getServer()->broadcastMessage(TextFormat::YELLOW . $player->getName() .  TextFormat::AQUA . " has tiered up his " . TextFormat::BOLD . $this->kit->getColoredName() . TextFormat::RESET . TextFormat::AQUA . " Kit to " . $tier . "!");
                    $player->getDataSession()->levelUpSacredKitTier($this->kit);
                }
            }, 25),
            new Reward("One Time Access", VanillaBlocks::WOOL()->setColor(DyeColor::RED())->asItem()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "One Time Access"), function (NexusPlayer $player) {
                $player->sendMessage(TextFormat::BOLD . TextFormat::YELLOW . "Looks like you didn't get a permanent sacred kit. You've discovered 1 kit from uncovering this holy box!");
                $player->getInventory()->addItem((new ChestKit($this->kit))->getItemForm());
            }, 100)
        ];
        $this->inventory = InvMenu::create(InvMenu::TYPE_CHEST);
        $this->inventory->setListener(InvMenu::readonly());
        $this->inventory->setName(TextFormat::AQUA . TextFormat::BOLD . "Holy Box");
        $glass = VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::GREEN())->asItem();
        $glass->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Rolling...");
        $this->actualInventory = $this->inventory->getInventory();
        for ($i = 0; $i <= 8; $i++) {
            $this->actualInventory->setItem($i, $glass);
        }
        for ($i = 18; $i <= 26; $i++) {
            $this->actualInventory->setItem($i, $glass);
        }
        $this->actualInventory->setItem(22, VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::LIGHT_GRAY())->asItem());
        $this->inventory->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory): void {
            $reward = $this->getReward();
            $callable = $reward->getCallback();
            $callable($player);
            $this->getHandler()->cancel();
        });
        $this->inventory->send($owner);
    }

    /**
     * @param int $currentTick
     */
    public function onRun(): void
    {
        if ($this->owner === null or $this->owner->isOnline() === false) {
            $this->getHandler()->cancel();
            return;
        }
        $this->ticks++;
        if ($this->ticks <= 40 and $this->ticks % 4 == 0) {
            $this->roll();
            return;
        }
        if ($this->ticks <= 80 and $this->ticks % 6 == 0) {
            $this->roll();
            return;
        }
        if ($this->ticks <= 120 and $this->ticks % 8 == 0) {
            $this->roll();
            return;
        }
        if ($this->ticks <= 160 and $this->ticks % 15 == 0) {
            $this->roll();
            return;
        }
        if ($this->ticks <= 250 and $this->ticks % 25 == 0) {
            if (mt_rand(1, 2) === 1) {
                $this->roll();
            }
        }
        if ($this->ticks === 251) {
            $this->inventory->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory): void {
                $reward = $this->selector[13];
                $this->owner->playXpLevelUpSound();
                $callable = $reward->getCallback();
                $callable($player);
                $this->getHandler()->cancel();
            });
            return;
        }
        if ($this->ticks >= 280) {
            $this->owner->removeCurrentWindow();
        }
    }

    /**
     * @return Reward
     */
    public function roll(): Reward
    {
        foreach ($this->selector as $index => $reward) {
            if ($index === 17) {
                break;
            }
            $this->selector[$index + 1] = $reward;
        }
        $this->selector[9] = $this->getReward();
        foreach ($this->selector as $index => $reward) {
            $this->actualInventory->setItem($index, $reward->getItem());
        }
        $this->owner->playNoteSound();
        return $this->selector[13] ?? $this->getReward();
    }

    /**
     * @param int $loop
     *
     * @return Reward
     */
    public function getReward(int $loop = 0): Reward
    {
        $chance = mt_rand(0, 100);
        $reward = $this->rewards[array_rand($this->rewards)];
        if ($loop >= 10) {
            return $reward;
        }
        if ($reward->getChance() <= $chance) {
            return $this->getReward($loop + 1);
        }
        return $reward;
    }
}