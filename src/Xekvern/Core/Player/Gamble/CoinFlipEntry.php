<?php

namespace Xekvern\Core\Player\Gamble;

use Xekvern\Core\Player\NexusPlayer;

class CoinFlipEntry {

    const MONEY = 0;
    const ENERGY = 1;

    /** @var NexusPlayer */
    private $owner;

    /** @var int */
    private $amount;

    /** @var string */
    private $color;

    /** @var int */
    private $type;

    /**
     * CoinFlipEntry constructor.
     *
     * @param NexusPlayer $owner
     * @param int $amount
     * @param string $color
     * @param int $type
     */
    public function __construct(NexusPlayer $owner, int $amount, string $color, int $type) {
        $this->owner = $owner;
        $this->amount = $amount;
        $this->color = $color;
        $this->type = $type;
    }

    /**
     * @return NexusPlayer
     */
    public function getOwner(): NexusPlayer {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getAmount(): int {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getColor(): string {
        return $this->color;
    }

    /**
     * @return int
     */
    public function getType(): int {
        return $this->type;
    }
}