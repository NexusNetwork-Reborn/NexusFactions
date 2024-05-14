<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Entity\Types;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Lightning extends Entity
{

    public static function getNetworkTypeId(): string
    {
        return EntityIds::LIGHTNING_BOLT;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.9, 0.3);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 1;
    }

    protected function getInitialGravity(): float
    {
        return 1;
    }

    /** @var float */
    public $width = 0.3;

    /** @var float */
    public $length = 0.9;

    /** @var float */
    public $height = 1.8;

    /** @var int */
    protected $age = 0;

    /** @var bool */
    protected $doneDamage = false;

    /**
     * @return string
     */
    public function getName(): string
    {
        return "Lightning";
    }


    /**
     * @param int $currentTick
     *
     * @return bool
     */
    public function onUpdate(int $currentTick): bool
    {
        if (!$this->doneDamage) {
            $this->doneDamage = true;
        }
        if ($this->age > 6 * 20) {
            $this->flagForDespawn();
        }
        $this->age++;
        return parent::onUpdate($currentTick);
    }
}
