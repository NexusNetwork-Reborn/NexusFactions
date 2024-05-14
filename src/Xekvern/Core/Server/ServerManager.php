<?php

namespace Xekvern\Core\Server;

use Exception;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Announcement\AnnouncementHandler;
use Xekvern\Core\Server\Area\AreaHandler;
use Xekvern\Core\Server\Auction\AuctionHandler;
use Xekvern\Core\Server\Crate\CrateHandler;
use Xekvern\Core\Server\Entity\EntityHandler;
use Xekvern\Core\Server\Fund\FundHandler;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Kit\KitHandler;
use Xekvern\Core\Server\Update\UpdateHandler;
use Xekvern\Core\Server\World\WorldHandler;
use Xekvern\Core\Server\NPC\NPCHandler;
use Xekvern\Core\Server\Price\PriceHandler;
use Xekvern\Core\Server\Watchdog\WatchdogHandler;

class ServerManager  {

    /** @var Nexus */
    private $core;

    /** @var ItemHandler */
    private $itemHandler; 

    /** @var CrateHandler */
    private $crateHandler;

    /** @var KitHandler */
    private $kitHandler;

    /** @var UpdateHandler */
    private $updateHandler;

    /** @var WorldHandler */
    private $worldHandler;

    /** @var AreaHandler */
    private $areaHandler;

    /** @var AnnouncementHandler */
    private $announcementHandler;

    /** @var NPCHandler */
    private $npcHandler;

    /** @var PriceHandler */
    private $priceHandler;

    /** @var EntityHandler */
    private $entityHandler;

    /** @var WatchdogHandler */
    private $watchdogHandler;

    /** @var AuctionHandler */
    private $auctionHandler;

    /** @var FundHandler */
    private $fundHandler;


    /**
     * ServerManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $this->initiateHandlers();
    }

    /**
     * @return WorldHandler
     */
    public function getWorldHandler(): WorldHandler {
        return $this->worldHandler;
    }

    /**
     * @return AreaHandler
     */
    public function getAreaHandler(): AreaHandler {
        return $this->areaHandler;
    }

    /**
     * @return ItemHandler
     */
    public function getItemHandler(): ItemHandler {
        return $this->itemHandler;
    }

    /**
     * @return CrateHandler
     */
    public function getCrateHandler(): CrateHandler {
        return $this->crateHandler;
    }

    /** 
     * @return KitHandler 
     * */
    public function getKitHandler(): KitHandler {
        return $this->kitHandler;
    }

    /**
     * @return UpdateHandler
     */
    public function getUpdateHandler(): UpdateHandler {
        return $this->updateHandler;
    }
    
    /**
     * @return AnnouncementHandler
     */
    public function getAnnouncementHandler(): AnnouncementHandler {
        return $this->announcementHandler;
    }

    /**
     * @return NPCHandler
     */
    public function getNPCHandler(): NPCHandler {
        return $this->npcHandler;
    }

    /** 
     * @return PriceHandler
     */
    public function getPriceHandler(): PriceHandler {
        return $this->priceHandler;
    }

    /**
     * @return EntityHandler
     */
    public function getEntityHandler(): EntityHandler {
        return $this->entityHandler;
    }

    /**
     * @return WatchdogHandler
     */
    public function getWatchdogHandler(): WatchdogHandler {
        return $this->watchdogHandler;
    }

    /**
     * @return AuctionHandler
     */
    public function getAuctionHandler(): AuctionHandler {
        return $this->auctionHandler;
    }

    /**
     * @return FundHandler
     */
    public function getFundHandler(): FundHandler {
        return $this->fundHandler;
    }

    /**
     * @return bool
     */
    public function initiateHandlers() : bool {
        $this->announcementHandler = new AnnouncementHandler($this->core);
        $this->areaHandler = new AreaHandler($this->core);
        $this->worldHandler = new WorldHandler($this->core);
        $this->itemHandler = new ItemHandler($this->core);
        $this->kitHandler = new KitHandler($this->core);
        $this->updateHandler = new UpdateHandler($this->core);
        $this->crateHandler = new CrateHandler($this->core);
        $this->npcHandler = new NPCHandler($this->core);
        $this->priceHandler = new PriceHandler($this->core);
        $this->entityHandler = new EntityHandler($this->core);
        $this->watchdogHandler = new WatchdogHandler($this->core);
        $this->auctionHandler = new AuctionHandler($this->core);
        $this->fundHandler = new FundHandler($this->core);
        return true;
    }
}