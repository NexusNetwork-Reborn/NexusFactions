<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Crate;

use pocketmine\item\Item;
use Xekvern\Core\Player\NexusPlayer;

class Reward {

    /** @var string */
    private $name;

    /** @var Item */
    private $item;

    /** @var callable */
    private $callback;

    /** @var int */
    private $chance;

    /**
     * Reward constructor.
     *
     * @param string $name
     * @param Item $item
     * @param callable $callable
     * @param int $chance
     */
    public function __construct(string $name, Item $item, callable $callable, int $chance) {
        $this->name = $name;
        $this->item = $item;
        $this->callback = $callable;
        $this->chance = $chance;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return Item
     */
    public function getItem(): Item {
        return $this->item;
    }

    /**
     * @param NexusPlayer|null $player
     *
     * @return Item
     */
    public function executeCallback(?NexusPlayer $player = null): Item {
        $callable = $this->callback;
        return $callable($player);
    }

    /**
     * @return callable
     */
    public function getCallback(): callable {
        return $this->callback;
    }

    /**
     * @return int
     */
    public function getChance(): int {
        return $this->chance;
    }
}