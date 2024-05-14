<?php

namespace Xekvern\Core\Session\Types;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\Bow;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class CESession
{

    /** @var NexusPlayer */
    private $owner;

    /** @var array */
    private $activeArmorEnchantments = [];

    /** @var array */
    private $activeHeldItemEnchantments = [];

    /** @var int */
    private $armorLuckModifier = 1;

    /** @var int */
    private $itemLuckModifier = 1;

    /** @var bool */
    private $hidingHealth = false;

    /** @var bool */
    private $dominated = false;

    /** @var bool */
    private $silenced = false;

    /** @var bool */
    private $bleeding = false;

    /** @var bool */
    private $weakened = false;

    /** @var bool */
    private $trapped = false;

    /** @var float */
    private $aegis = 1.0;

    /** @var int */
    private $frenzyHits = 0;

    /** @var int */
    private $lastFrenzyHit = 0;

    /** @var bool */
    private $divineProtected = false;

    /** @var null|Position */
    private $offensiveProcLocation = null;

    /**
     * CESession constructor.
     *
     * @param NexusPlayer $owner
     */
    public function __construct(NexusPlayer $owner)
    {
        $this->owner = $owner;
    }

    public function reset(): void
    {
        $this->hidingHealth = false;
        $this->dominated = false;
        $this->silenced = false;
        $this->bleeding = false;
        $this->weakened = false;
        $this->trapped = false;
    }

    /**
     * @param NexusPlayer $owner
     */
    public function setOwner(NexusPlayer $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return NexusPlayer
     */
    public function getOwner(): NexusPlayer
    {
        return $this->owner;
    }

    public function setActiveArmorEnchantments(): void
    {
        $this->activeArmorEnchantments = [];
        if ($this->owner->isClosed()) {
            return;
        }
        $inventory = $this->owner->getArmorInventory();
        foreach ($inventory->getContents() as $item) {
            if (!$item->hasEnchantments()) {
                continue;
            }
            foreach ($item->getEnchantments() as $enchantment) {
                $type = $enchantment->getType();
                if (!$type instanceof Enchantment) {
                    continue;
                }
                if (isset($this->activeArmorEnchantments[$type->getEventType()][$enchantment->getType()->getName()])) {
                    $this->activeArmorEnchantments[$type->getEventType()][$enchantment->getType()->getName()] = $enchantment->getLevel();
                }
                $this->activeArmorEnchantments[$type->getEventType()][$enchantment->getType()->getName()] = $enchantment;
            }
        }
        if ($this->silenced === true) {
            $this->armorLuckModifier /= 2;
        }
    }

    public function setActiveHeldItemEnchantments(): void
    {
        $this->activeHeldItemEnchantments = [];
        $item = $this->owner->getInventory()->getItemInHand();
        if (!$item->hasEnchantments()) {
            return;
        }
        if ($item instanceof Sword or $item instanceof Bow or $item instanceof Tool) {
            foreach ($item->getEnchantments() as $enchantment) {
                $type = $enchantment->getType();
                if (!$type instanceof Enchantment) {
                    continue;
                }
                if (isset($this->activeHeldItemEnchantments[$type->getEventType()][EnchantmentIdMap::getInstance()->toId($enchantment->getType())])) {
                    $this->activeHeldItemEnchantments[$type->getEventType()][EnchantmentIdMap::getInstance()->toId($enchantment->getType())] = $enchantment->setLevel($this->activeHeldItemEnchantments[$type->getEventType()][EnchantmentIdMap::getInstance()->toId($enchantment->getType())]->getLevel() + $enchantment->getLevel());
                }
                $this->activeHeldItemEnchantments[$type->getEventType()][EnchantmentIdMap::getInstance()->toId($enchantment->getType())] = $enchantment;
            }
        }
        if ($this->hasAegis()) {
            $this->itemLuckModifier *= $this->aegis;
        }
    }

    /**
     * @return array
     */
    public function getActiveEnchantments(): array
    {
        $active = [];
        foreach ($this->activeArmorEnchantments as $eventType => $enchantments) {
            foreach ($enchantments as $id => $level) {
                $active[$eventType][$id] = $level;
            }
        }
        foreach ($this->activeHeldItemEnchantments as $eventType => $enchantments) {
            foreach ($enchantments as $id => $level) {
                $active[$eventType][$id] = $level;
            }
        }
        return $active;
    }

    /**
     * @param \pocketmine\item\enchantment\Enchantment $enchantment
     *
     * @return int
     */
    public function getEnchantmentLevel(\pocketmine\item\enchantment\Enchantment $enchantment): int
    {
        if ($enchantment instanceof Enchantment) {
            if (isset($this->activeArmorEnchantments[$enchantment->getEventType()][$enchantment->getName()])) {
                return $this->activeArmorEnchantments[$enchantment->getEventType()][$enchantment->getName()]->getLevel();
            }
            if (isset($this->activeHeldItemEnchantments[$enchantment->getEventType()][$enchantment->getName()])) {
                return $this->activeHeldItemEnchantments[$enchantment->getEventType()][$enchantment->getName()]->getLevel();
            }
        }
        return 0;
    }

    /**
     * @return int
     */
    public function getItemLuckModifier(): int
    {
        return $this->itemLuckModifier;
    }

    /**
     * @return int
     */
    public function getArmorLuckModifier(): int
    {
        return $this->armorLuckModifier;
    }

    /**
     * @return bool
     */
    public function isHidingHealth(): bool
    {
        return $this->hidingHealth;
    }

    /**
     * @param bool $hidingHealth
     */
    public function setHidingHealth(bool $hidingHealth): void
    {
        $this->hidingHealth = $hidingHealth;
    }

    /**
     * @return bool
     */
    public function isDominated(): bool
    {
        return $this->dominated;
    }

    /**
     * @param bool $dominated
     */
    public function setDominated(bool $dominated): void
    {
        $this->dominated = $dominated;
        if ($dominated) {
            $this->offensiveProcLocation = $this->owner->getPosition();
        } else {
            $this->offensiveProcLocation = null;
        }
    }

    /**
     * @return bool
     */
    public function isSilenced(): bool
    {
        return $this->silenced;
    }

    /**
     * @param bool $silenced
     */
    public function setSilenced(bool $silenced): void
    {
        $this->silenced = $silenced;
        if ($silenced) {
            $this->offensiveProcLocation = $this->owner->getPosition();
        } else {
            $this->offensiveProcLocation = null;
        }
    }

    /**
     * @return bool
     */
    public function isBleeding(): bool
    {
        return $this->bleeding;
    }

    /**
     * @param bool $bleeding
     */
    public function setBleeding(bool $bleeding): void
    {
        $this->bleeding = $bleeding;
        if ($bleeding) {
            $this->offensiveProcLocation = $this->owner->getPosition();
        } else {
            $this->offensiveProcLocation = null;
        }
    }

    /**
     * @return bool
     */
    public function isWeakened(): bool
    {
        return $this->weakened;
    }

    /**
     * @param bool $weakened
     */
    public function setWeakened(bool $weakened): void
    {
        $this->weakened = $weakened;
        if ($weakened) {
            $this->offensiveProcLocation = $this->owner->getPosition();
        } else {
            $this->offensiveProcLocation = null;
        }
    }

    /**
     * @return int
     */
    public function getFrenzyHits(): int
    {
        if ((time() - $this->lastFrenzyHit) > 60) {
            $this->frenzyHits = 0;
        }
        return $this->frenzyHits;
    }

    public function addFrenzyHits(): void
    {
        if ($this->lastFrenzyHit === 0) {
            $this->lastFrenzyHit = time();
        } elseif ((time() - $this->lastFrenzyHit) >= 60) {
            $this->frenzyHits = 0;
        }
        ++$this->frenzyHits;
        $this->lastFrenzyHit = time();
    }

    public function resetFrenzyHits(): void
    {
        $this->frenzyHits = 0;
        $this->owner->sendPopup(TextFormat::RED . TextFormat::BOLD . "RESET " . TextFormat::RESET . TextFormat::GRAY . "+0%%%");
    }

    /**
     * @return bool
     */
    public function hasAegis(): bool
    {
        return $this->aegis < 1;
    }

    /**
     * @param float $aegis
     */
    public function setAegis(float $aegis): void
    {
        $this->aegis = $aegis;
    }

    /**
     * @return bool
     */
    public function isTrapped(): bool
    {
        return $this->trapped;
    }

    /**
     * @param bool $trapped
     */
    public function setTrapped(bool $trapped): void
    {
        $this->trapped = $trapped;
        $this->owner->setImmobile($trapped);
        if ($trapped) {
            $this->offensiveProcLocation = $this->owner->getPosition();
        } else {
            $this->offensiveProcLocation = null;
        }
    }

    /**
     * @return bool
     */
    public function isDivineProtected(): bool
    {
        return $this->divineProtected;
    }

    /**
     * @param bool $divineProtected
     */
    public function setDivineProtected(bool $divineProtected): void
    {
        $this->divineProtected = $divineProtected;
    }

    /**
     * @return Position|null
     */
    public function getOffensiveProcLocation(): ?Position
    {
        return $this->offensiveProcLocation;
    }

    /**
     * @param Position|null $offensiveProcLocation
     */
    public function setOffensiveProcLocation(?Position $offensiveProcLocation): void
    {
        $this->offensiveProcLocation = $offensiveProcLocation;
    }
}
