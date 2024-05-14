<?php

namespace Xekvern\Core\Server\Auction;

use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;

class AuctionEntry {

    const MAX_TIME = 43200;

    /** @var Item */
    private $item;

    /** @var int */
    private $count;

    /** @var int */
    private $id;

    /** @var int */
    private $startTime;

    /** @var int */
    private $buyPrice;

    /** @var string */
    private $seller;

    /**
     * AuctionEntry constructor.
     *
     * @param Item $item
     * @param string $seller
     * @param int $identifier
     * @param int $startTime
     * @param int $buyPrice
     */
    public function __construct(Item $item, int $count, string $seller, int $identifier, int $startTime, int $buyPrice) {
        $this->item = $item;
        $this->count = $count;
        $this->seller = $seller;
        $this->id = $identifier;
        $this->startTime = $startTime;
        $this->buyPrice = $buyPrice;
    }

    /**
     * @return int
     */
    public function getIdentifier(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSeller(): string {
        return $this->seller;
    }

    /**
     * @return Item
     */
    public function getItem(): Item {
        return $this->item;
    }

    /**
     * @return int
     */
    public function getStartTime(): int {
        return $this->startTime;
    }
    
    /**
     * @return int
     */
    public function getCount(): int {
        return $this->count;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool {
        return $this->getStartTime() + self::MAX_TIME < time();
    }

    /** 
     * @return int
     */
    public function getRemainingTime(): int {
        return $this->startTime + self::MAX_TIME - time();
    }

    /**
     * @return int
     */
    public function getBuyPrice(): int {
        return $this->buyPrice;
    }

    /**
     * @param NexusPlayer $player
     * @param int $amount
     */
    public function buy(NexusPlayer $player): void {
        $item = $this->item;
        $count = $this->count;
        $price = $this->getBuyPrice();
        if($player->getName() === $this->getSeller()) {
            $player->sendMessage(Translation::getMessage("invalidAuction"));
            return;
        }
        if($player->getDataSession()->getBalance() < $price) {
            $player->sendMessage(Translation::getMessage("notEnoughMoney"));
            return;
        }
        if($this->isExpired()) {
            $player->sendMessage(Translation::getMessage("invalidAuction"));
            return;
        }
        $seller = Server::getInstance()->getPlayerExact($this->seller);
        $name = $this->getItem()->hasCustomName() ? $this->getItem()->getCustomName() : $this->getItem()->getName();
        if($seller instanceof NexusPlayer) {
            if(!$seller->isLoaded()) {
                $seller->sendMessage(Translation::getMessage("errorOccurred"));
                return;
            }
            $seller->getDataSession()->addToBalance($price);
            $seller->sendMessage(Translation::getMessage("buyAuction", [
                "item" => $name,
                "name" => TextFormat::DARK_PURPLE . $player->getName(),
                "amount" => TextFormat::YELLOW . "$" . number_format($price),
            ]));
            $seller->playXpLevelUpSound();
        }
        else {
            $stmt = Nexus::getInstance()->getMySQLProvider()->getDatabase()->prepare("UPDATE stats SET balance = balance + ? WHERE username = ?");
            $stmt->bind_param("is", $price, $this->seller);
            $stmt->execute();
            $stmt->close();
        }
        $player->getDataSession()->subtractFromBalance($price);
        $player->getInventory()->addItem($this->item->setCount($count));
        $player->playNoteSound();
        Nexus::getInstance()->getServerManager()->getAuctionHandler()->removeAuction($this->getIdentifier());
    }

    /**
     * @param NexusPlayer $seller
     *
     * @throws TranslationException
     */
    public function cancel(NexusPlayer $seller): void {
        if($seller->getName() === $this->seller) {
            $seller->sendMessage(Translation::getMessage("noSell"));
            $seller->getInventory()->addItem($this->item->setCount($this->count));
            Nexus::getInstance()->getServerManager()->getAuctionHandler()->removeAuction($this->getIdentifier());
        }
        else {
            $seller->sendMessage(Translation::getMessage("invalidPlayer"));
        }
    }

    /** 
     * @return array
     */
    public function getAuctionData(): array {
        return [
            "item" => Nexus::encodeItem($this->item),
            "count" => $this->count,
            "seller" => $this->seller,
            "identifier" => $this->getIdentifier(),
            "startTime" => $this->startTime,
            "buyPrice" => $this->buyPrice
        ];
    }
}