<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Crate\Task;

use pocketmine\entity\Entity;
use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddItemActorPacket;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\LavaParticle;
use pocketmine\world\sound\BlastFurnaceSound;
use pocketmine\world\sound\ChestCloseSound;
use pocketmine\world\sound\ChestOpenSound;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\world\sound\LaunchSound;
use Xekvern\Core\Entity\Types\Lightning;
use Xekvern\Core\Utils\FloatingTextParticle;

class AnimationTask extends Task {

    /** @var int */
    private $runs = 0;

    /** @var Crate */
    private $crate;

    /** @var NexusPlayer */
    private $player;

    /** @var int */
    private $id = null;

    /** @var Position */
    private $pos;

    /** @var FloatingTextParticle */
    private $ftp;

    /** @var int */
    private $count;

    /** @var Reward[] */
    private $rewards = [];


    /**
     * AnimationTask constructor.
     *
     * @param Crate $crate
     * @param NexusPlayer $player
     * @param int $count
     */
    public function __construct(Crate $crate, NexusPlayer $player, int $count) {

        $this->crate = $crate;
        $player->setRunningCrateAnimation();
        $this->player = $player;
        $this->count = $count;
    }

    /**
     * @param Reward $reward
     */
    public function spawnItemEntity(Reward $reward) {
        if($this->id !== null){
            $this->removeItemEntity();
        }
        $item = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($reward->getItem()));
        $this->id = Entity::nextRuntimeId();
        $pk = AddItemActorPacket::create(
            $this->id,
            $this->id,
            $item,
            $this->crate->getPosition()->add(0.5, 0.75, 0.5),
            null,
            [],
            false,
        );
        $this->player->getNetworkSession()->sendDataPacket($pk);
    }

    public function removeItemEntity() {
        $pk = RemoveActorPacket::create($this->id);
        $this->player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     */
    public function onRun() : void {
        if($this->player->isClosed()) {
            new CancelTaskException();
            return;
        }
        $this->runs += 1;
        $position = $this->crate->getPosition();
        if($this->runs === 1) {
            $this->player->broadcastSound(new ChestOpenSound(), [$this->player]);
            $pk = BlockEventPacket::create(
                new BlockPosition($position->getFloorX(), $position->getFloorY(), $position->getFloorZ()),
                1,
                1
            );
            $this->player->getNetworkSession()->sendDataPacket($pk);
        }
        if($this->runs === 2) {
            $this->player->broadcastSound(new LaunchSound());
        }
        if($this->runs < 16) {

            $cx = $position->getX() + 0.5;
            $cy = $position->getY() + 1;
            $cz = $position->getZ() + 0.5;
            $radius = 1;
            for($i = 0; $i < 21; $i += 1.1) {
                $x = $cx + ($radius * cos($i));
                $z = $cz + ($radius * sin($i));
                $pos = new Vector3($x, $cy, $z);
                $position->world->addParticle($pos, new FlameParticle(), [$this->player]);
            }

            $this->spawnItemEntity($this->crate->getRewards()[array_rand($this->crate->getRewards())]);
            $this->player->broadcastSound(new BlastFurnaceSound(), [$this->player]);

        }
        if($this->runs === 17) {
            for($i = 0; $i < $this->count; $i++) {
                $this->rewards[] = $this->crate->getReward();
            }
            $bestReward = $this->getMostValuableReward();
            foreach($this->rewards as $reward) {
                $callable = $reward->getCallback();
                $callable($this->player);
            }
            $cx = $position->getX() + 0.5;
            $cy = $position->getY() + 1;
            $cz = $position->getZ() + 0.5;
            $radius = 1;
            for($i = 0; $i < 21; $i += 1.1) {
                $x = $cx + ($radius * cos($i));
                $z = $cz + ($radius * sin($i));
                $pos = new Vector3($x, $cy, $z);
                $position->world->addParticle($pos, new LavaParticle, [$this->player]);
            }
            $this->player->broadcastSound(new ExplodeSound(), [$this->player]);
            $this->spawnItemEntity($bestReward);
            $this->crate->showReward($bestReward, $this->player);
        }
        if($this->runs === 20) {
            $this->player->broadcastSound(new ChestCloseSound(), [$this->player]);
            $pk = BlockEventPacket::create(
                new BlockPosition($position->getFloorX(), $position->getFloorY(), $position->getFloorZ()),
                1,
                0
            );
            $this->player->getNetworkSession()->sendDataPacket($pk);
            $this->removeItemEntity();
            $this->crate->updateTo($this->player);
            $this->player->setRunningCrateAnimation(false);
            new CancelTaskException();
        }
    }

    public function getMostValuableReward() : ?Reward {
        if(empty($this->rewards)) return null;

        // Sort by chance
        usort($this->rewards, function($a, $b) {
           return $a->getChance() < $b->getChance() ? -1 : 1;
        });

        return $this->rewards[0];
    }

    public function spawnItemName(?Reward $bestReward) {
        $position = $this->crate->getPosition();
        $position->y = $position->y + 1.25;

        $this->ftp = new FloatingTextParticle($position, (string) rand(0, PHP_INT_MAX >> 8), $bestReward->getName());
        $this->ftp->spawn($this->player);
    }

    public function despawnItemName() {
        if($this->ftp instanceof FloatingTextParticle) {
            $this->ftp->despawn($this->player);
        }
    }
}