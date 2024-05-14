<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\effect\Effect;
use pocketmine\item\enchantment\Enchantment as VanillaEnchantment;
use pocketmine\item\enchantment\ItemFlags;
use Xekvern\Core\Server\Item\Enchantment\Utils\EnchantmentIdentifiers;

abstract class Enchantment extends \pocketmine\item\enchantment\Enchantment implements EnchantmentIdentifiers {

    const RARITY_GODLY = 0;

    const DAMAGE = 0;

    const BREAK = 1;

    const EFFECT_ADD = 2;

    const MOVE = 3;

    const DEATH = 4;

    const SHOOT = 5;

    const INTERACT = 6;

    const DAMAGE_BY = 7;

    /** @var callable */
    protected $callable;

    /** @var string */
    private $description;

    /** @var int */
    private $eventType;

    /** @var int */
    private $flagType;
    
    /** @var Effect|null */
    private Effect|null $effect = null;

    /**
     * Enchantment constructor.
     *
     * @param int $id
     * @param string $name
     * @param int $rarity
     * @param string $description
     * @param int $eventType
     * @param int $flag
     * @param int $maxLevel
     */
    public function __construct(string $name, int $rarity, string $description, int $eventType, int $flag, int $maxLevel, Effect $effect = null) {
        $this->description = $description;
        $this->eventType = $eventType;
        $this->flagType = $flag;
        $this->effect = $effect;
        parent::__construct($name, $rarity, $flag, ItemFlags::NONE, $maxLevel);
    }

    /**
     * @return int
     */
    public function getEventType(): int {
        return $this->eventType;
    }

    /**
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * @return callable
     */
    public function getCallable(): callable {
        return $this->callable;
    }

    /**
     * @return Effect|null
     */
    public function getEffect() : ?Effect {
        return $this->effect;
    }

    public static function getEnchantment(int $id): VanillaEnchantment {
        return EnchantmentIdMap::getInstance()->fromId($id);
    }
}