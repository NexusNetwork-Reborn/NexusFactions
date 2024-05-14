<?php

/**
 * Imported from Teaspoon
 */

declare(strict_types=1);

namespace Xekvern\Core\Server\Entity\Types;


use InvalidArgumentException;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Explosive;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityPreExplodeEvent;
use pocketmine\item\Durable;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use pocketmine\world\Explosion;
use pocketmine\world\Position;
use Xekvern\Core\Player\NexusPlayer;

final class Creeper extends Living implements Explosive
{

    protected int $fuse = 25;
    protected bool $ignited = false;
    protected bool $powered = false;

    public static function getNetworkTypeId(): string {
        return EntityIds::CREEPER;
    }

    public function entityBaseTick(int $tickDiff = 1): bool {
        $parent = parent::entityBaseTick($tickDiff);
        if ($this->isFlaggedForDespawn() or $this->isClosed()) {
            return false;
        }
        if ($this->getFuse() < 0) $this->setFuse(25);

        if ($this->isIgnited()) {
            $fuse = $this->getFuse() - $tickDiff;
            $this->setFuse($fuse);
            if ($fuse <= 0) $this->explode();
        }
        return $parent;
    }

    public function getFuse(): int {
        return $this->fuse;
    }

    public function setFuse(int $fuse): void {
        if ($fuse < 0 || $fuse > 32767) {
            throw new InvalidArgumentException("Fuse must be in the range 0-32767");
        }
        $this->fuse = $fuse;
        $this->networkPropertiesDirty = true;
    }

    public function isIgnited(): bool {
        return $this->ignited;
    }

    public function setIgnited(bool $ignited = true): void {
        $this->ignited = $ignited;
        $this->networkPropertiesDirty = true;
    }

    public function explode(): void {
        $this->flagForDespawn();
        $ev = new EntityPreExplodeEvent($this, 3);
        $ev->setBlockBreaking(true);
        $ev->call();

        if (!$ev->isCancelled()) {
            $explosion = new Explosion(Position::fromObject($this->location->add(0, $this->size->getHeight() / 2, 0), $this->getWorld()), $ev->getRadius(), $this);
            if ($ev->isBlockBreaking()) {
                $explosion->explodeA();
            }
            $explosion->explodeB();
        }
    }

    public function attack(EntityDamageEvent $source): void {
        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if (!$damager instanceof NexusPlayer) return;
            $item = $damager->getInventory()->getItemInHand();

            if ($item->getTypeId() == ItemTypeIds::FLINT_AND_STEEL) {
                if ($item instanceof Durable) $item->setDamage($item->getDamage() + 2);
                $this->setIgnited();
            }
        }

        parent::attack($source);
    }

    public function getDrops(): array {
        return [VanillaItems::GUNPOWDER()->setCount(mt_rand(0, 2))];
    }

    public function getName(): string {
        return "Creeper";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(1.7, 0.6);
    }

    protected function syncNetworkData(EntityMetadataCollection $properties): void {
        parent::syncNetworkData($properties);
        $properties->setGenericFlag(9, $this->isPowered());
        $properties->setGenericFlag(10, $this->isIgnited());

        $properties->setByte(EntityMetadataFlags::POWERED, (int) $this->powered);
        $properties->setByte(EntityMetadataFlags::IGNITED, (int) $this->ignited);
    }

    public function isPowered(): bool {
        return $this->powered;
    }
}
