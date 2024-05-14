<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Price;

use pocketmine\item\Item;

class ShopPlace {

    /** @var string */
    private $name;

    /** @var Item */
    private $item;

    /** @var PriceEntry[] */
    private $entries = [];

    /**
     * ShopPlace constructor.
     *
     * @param string $name
     * @param Item $item
     * @param array $entries
     */
    public function __construct(string $name, Item $item, array $entries) {
        $this->name = $name;
        $this->item = $item;
        foreach($entries as $entry) {
            $this->entries[$entry->getName()] = $entry;
        }
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
    public function getItem() {
        return $this->item;
    }

    /**
     * @return PriceEntry[]
     */
    public function getEntries(): array {
        return $this->entries;
    }

    /**
     * @param string $name
     *
     * @return PriceEntry|null
     */
    public function getEntry(string $name): ?PriceEntry {
        return $this->entries[$name] ?? null;
    }
}