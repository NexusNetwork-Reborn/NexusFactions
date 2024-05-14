<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Crate;

use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Crate\Event\CrateOpenEvent;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\translation\TranslatonException;
use pocketmine\world\Position;
use Xekvern\Core\Server\Crate\Task\AnimationTask;
use Xekvern\Core\Server\Crate\Task\OpenCrateTask;

abstract class Crate {

    const BOSS = "Boss";
    const ULTRA = "Ultra";
    const EPIC = "Epic";
    const LEGENDARY = "Legendary";

    /** @var string */
    private $name;

    /** @var Position */
    private $position;

    /** @var Reward[] */
    private $rewards;

    /**
     * Crate constructor.
     *
     * @param string $name
     * @param Position $position
     * @param Reward[] $rewards
     */
    public function __construct(string $name, Position $position, array $rewards) {
        $this->name = $name;
        $this->position = $position;
        $this->rewards = $rewards;
    }

    /**
     * @param NexusPlayer $player
     */
    abstract public function spawnTo(NexusPlayer $player): void;

    /**
     * @param NexusPlayer $player
     */
    abstract public function updateTo(NexusPlayer $player): void;

    /**
     * @param NexusPlayer $player
     */
    abstract public function despawnTo(NexusPlayer $player): void;

    /**
     * @param Reward $reward
     * @param NexusPlayer $player
     */
    abstract public function showReward(Reward $reward, NexusPlayer $player): void;

    /**
     * @param Reward $reward
     *
     * @return string
     */
    abstract public function getRewardDisplayName(Reward $reward): string;

    /**
     * @param NexusPlayer $player
     *
     * @throws TranslatonException
     */
    public function try(NexusPlayer $player, int $count = null): void {
        if($count < 0) return; // Safety reasons.
        $keys = $player->getDataSession()->getKeys($this);
        if($player->isRunningCrateAnimation() === true) {
            $player->sendMessage(Translation::getMessage("animationAlreadyRunning"));
            $player->knockBack(0, $player->getPosition()->getX() - $this->position->getX(), $player->getPosition()->getZ() - $this->position->getZ(), 1);
            return;
        }
        if($player->getInventory()->getSize() === count($player->getInventory()->getContents())) {
            $player->sendMessage(Translation::getMessage("fullInventory"));
            $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Full Inventory", TextFormat::GRAY . "Clear out your inventory!");
            $player->playErrorSound();
            $player->knockBack(0, $player->getPosition()->getX() - $this->position->getX(), $player->getPosition()->getZ() - $this->position->getZ(), 1);
            return;
        }
        if($player->getDataSession()->getKeys($this) <= 0) {
            $player->sendMessage(Translation::getMessage("noKeys"));
            $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "No Keys", TextFormat::GRAY . "You do not have keys for this crate!");
            $player->playErrorSound();
            $player->knockBack(0, $player->getPosition()->getX() - $this->position->getX(), $player->getPosition()->getZ() - $this->position->getZ(), 1);
            return;
        }
        if($player->isSneaking() and $player->getDataSession()->getKeys($this) > 1) {
            $slots = $player->getInventory()->getSize() - count($player->getInventory()->getContents());
            $crates = $player->getDataSession()->getKeys($this) > $slots ? $slots : $player->getDataSession()->getKeys($this);
            $player->getDataSession()->removeKeys($this, $crates);
            $event = new CrateOpenEvent($player, $this, $crates);
            $event->call();
            Nexus::getInstance()->getScheduler()->scheduleRepeatingTask(new AnimationTask($this, $player, $crates), 5);
            return;
        }
        $count = 1;
        $player->getDataSession()->removeKeys($this, $count);
        $event = new CrateOpenEvent($player, $this, $count);
        $event->call();
        Nexus::getInstance()->getScheduler()->scheduleRepeatingTask(new AnimationTask($this, $player, $count), 5);
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position {
        return $this->position;
    }

    /**
     * @return Reward[]
     */
    public function getRewards(): array {
        return $this->rewards;
    }

    /**
     * @param int $loop
     *
     * @return Reward
     */
    public function getReward(int $loop = 0): Reward {
        $chance = mt_rand(0, 100);
        $reward = $this->rewards[array_rand($this->rewards)];
        if($loop >= 10) {
            return $reward;
        }
        if($reward->getChance() <= $chance) {
            return $this->getReward($loop + 1);
        }
        return $reward;
    }
}
