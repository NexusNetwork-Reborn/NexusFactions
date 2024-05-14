<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Price;

use Xekvern\Core\Nexus;
use libs\form\FormIcon;
use libs\form\MenuOption;
use pocketmine\item\Item;
use pocketmine\Server;

class PriceEntry {

    /** @var int|null */
    private $sellPrice;

    /** @var int|null */
    private $buyPrice;

    /** @var Item */
    private $item;

    /** @var string */
    private $name;

    /** @var string|null */
    private $permission;

    /**
     * PriceEntry constructor.
     *
     * @param Item $item
     * @param string|null $name
     * @param int|null $sellPrice
     * @param int|null $buyPrice
     * @param string|null $permission
     */
    public function __construct(Item $item, ?string $name = null, ?int $sellPrice = null, ?int $buyPrice = null, ?string $permission = null) {
        $this->item = $item;
        $this->name = $name;
        if($name === null) {
            $this->name = $this->item->getName();
        }
        $this->sellPrice = $sellPrice;
        $this->buyPrice = $buyPrice;
        $this->permission = $permission;
    }

    /**
     * @return Item
     */
    public function getItem(): Item {
        return $this->item;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return int|null
     */
    public function getSellPrice(): ?int {
        return $this->sellPrice;
    }

    /**
     * @return int|null
     */
    public function getBuyPrice(): ?int {
        return $this->buyPrice;
    }

    /**
     * @return string|null
     */
    public function getPermission(): ?string {
        return $this->permission;
    }

    public function equal(Item $item): bool {
        return $this->item->equals($item);
    }

    /**
     * @return MenuOption
     */
    public function toMenuOption(): MenuOption {
        $link = "http://avengetech.me/items/{ID}-{DAMAGE}.png";
        $link = str_replace("{ID}", (string)$this->item->getTypeId(), $link);
        //$link = str_replace("{DAMAGE}", $this->item->getDamage(), $link);
        $icon = new FormIcon($link, FormIcon::IMAGE_TYPE_URL);
        return new MenuOption($this->name, $icon);
    }
}