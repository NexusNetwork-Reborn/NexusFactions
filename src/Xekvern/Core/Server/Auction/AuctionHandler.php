<?php

namespace Xekvern\Core\Server\Auction;

use pocketmine\utils\Config;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Auction\Task\SaveAuctionTask;

class AuctionHandler {

    /** @var Nexus */
    private $core;

    /** @var array<int, Auction> */
    private array $auctions = [];

    /**
     * AuctionHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $auctions = [];
        $auctionFolder = $this->getAuctionFolder();
        @mkdir($auctionFolder);
        if (!is_dir($auctionFolder) || !is_readable($auctionFolder)) {
            return;
        }
        foreach (scandir($auctionFolder) as $file) {
            if ($file === "." || $file === ".." || pathinfo($file, PATHINFO_EXTENSION) !== 'auction') {
                continue;
            }
            $auctionData = unserialize(file_get_contents($auctionFolder . $file));
            if (!is_array($auctionData)) {
                continue;
            }
            $auctions[$auctionData['identifier']] = new AuctionEntry(
                Nexus::decodeItem($auctionData['item']),
                $auctionData['count'],
                $auctionData['seller'],
                $auctionData['identifier'],
                $auctionData['startTime'],
                $auctionData['buyPrice']
            );
        }
        $this->auctions = $auctions;
    }

    /**
     * @param int $identifier
     */
    public function getAuction(int $identifier): ?AuctionEntry {
        return $this->auctions[$identifier] ?? null;
    }

     /**
     * @param NexusPlayer $player
     *
     * @return AuctionEntry[]
     */
    public function getAuctionsOf(NexusPlayer $player): array {
        $entries = [];
        foreach($this->auctions as $entry) {
            if($entry->getSeller() === $player->getName()) {
                $entries[] = $entry;
            }
        }
        return $entries;
    }

    /**
     * @param AuctionEntry $auction
     */
    public function addAuction(AuctionEntry $auction): void {
        $this->auctions[$auction->getIdentifier()] = $auction;
        $this->saveAuction($auction);
    }

    /**
     * @param int $identifier
     */
    public function removeAuction(int $identifier): void {
        unset($this->auctions[$identifier]);
        unlink($this->getAuctionFolder() . $identifier . ".auction");
    }

    /**
     * @param Auction $auction
     */
    public function saveAuction(AuctionEntry $auction): void {
        Nexus::getInstance()->getServer()->getAsyncPool()->submitTask(
            new SaveAuctionTask($this->getAuctionFolder() . $auction->getIdentifier() . ".auction", $auction->getAuctionData())
        );
    }

    /**
     * @return int
     */
    public function getNewIdentifier(): int {
        $identifier = 0;
        while (isset($this->auctions[$identifier])) $identifier++;
        return $identifier;
    }

    /**
     * @return AuctionEntry[]
     */
    public function getAuctions(): array {
        return $this->auctions;
    }

    /**
     * @return string
     */
    public function getAuctionFolder(): string {
        return $this->core->getDataFolder() . "auctions/";
    }

    /**
     * @return array
     */
    public function arrayToPage(array $array, ?int $page, int $separator): array
    {
        $result = [];

        $pageMax = ceil(count($array) / $separator);
        $min = ($page * $separator) - $separator;

        $count = 1;
        $max = $min + $separator;

        foreach ($array as $item) {
            if ($count > $max) {
                continue;
            } else if ($count > $min) {
                $result[] = $item;
            }
            $count++;
        }
        return [$pageMax, $result];
    }
}