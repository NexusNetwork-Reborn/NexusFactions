<?php

namespace Xekvern\Core\Player\Combat\Boss\Types;

use Xekvern\Core\Player\Combat\Boss\Boss;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;

class Minion extends Boss {

    /**
     * Minion constructor.
     *
     * @param Location $location
     * @param Skin $skin
     * @param CompoundTag $nbt
     *
     */
    public function __construct(Location $location, ?Skin $skin = null, ?CompoundTag $nbt = null) {
		parent::__construct($location, $skin, $nbt);
        $this->skin = $skin;
        $this->setMaxHealth(100);
        $this->setHealth(100);
        $this->setNametag(TextFormat::BOLD . TextFormat::DARK_GREEN . "Minion\n" . TextFormat::RESET . TextFormat::WHITE . $this->getHealth() . TextFormat::BOLD . TextFormat::RED . " HP");
        $this->setScale(1);
        $this->attackDamage = 0.6;
        $this->speed = 2;
        $this->attackWait = 20;
        $this->regenerationRate = 2;
    }

    /**
     * @param int $tickDiff
     *
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool {
        $this->setNametag(TextFormat::BOLD . TextFormat::DARK_GREEN . "Minion " . TextFormat::RESET . TextFormat::WHITE . $this->getHealth() . "/" . $this->getMaxHealth());
        return parent::entityBaseTick($tickDiff);
    }
}