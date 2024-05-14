<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\NPC;

use Xekvern\Core\Nexus;
use Xekvern\Core\Server\NPC\Task\NPCHeartbeatTask;
use Xekvern\Core\Server\NPC\Types\Alchemist;
use Xekvern\Core\Server\NPC\Types\Auctioneer;
use Xekvern\Core\Server\NPC\Types\Blacksmith;
use Xekvern\Core\Server\NPC\Types\Enchanter;
use Xekvern\Core\Server\NPC\Types\LootboxMerchant;
use Xekvern\Core\Server\NPC\Types\Merchant;
use Xekvern\Core\Server\NPC\Types\MysteriousMerchant;
use Xekvern\Core\Server\NPC\Types\QuestGiver;
use Xekvern\Core\Server\NPC\Types\Tinker;
use Xekvern\Core\Server\NPC\Types\Updater;
use Xekvern\Core\Server\NPC\Types\Voldemort;

class NPCHandler {

    /** @var Nexus */
    private $core;

    /** @var NPC[] */
    private $npcs = [];

    /**
     * NPCHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $core->getServer()->getPluginManager()->registerEvents(new NPCEvents($core), $core);
        $core->getScheduler()->scheduleRepeatingTask(new NPCHeartbeatTask($this), 100);
        $this->init();
    }

    public function init() {
        if(file_exists($this->core->getDataFolder() . "skins/") == false) {
            mkdir($this->core->getDataFolder() . "skins/");
            $this->core->saveResource("skins/lootboxmerchant.png");
            $this->core->saveResource("skins/mysteriousmerchant.png");
            $this->core->saveResource("skins/tinker.png");
            $this->core->saveResource("skins/alchemist.png");
            $this->core->saveResource("skins/auctioneer.png");
            $this->core->saveResource("skins/voldemort.png");
            $this->core->saveResource("skins/updater.png");
            $this->core->saveResource("skins/enchanter.png");
        }
        $this->addNPC(new LootboxMerchant());
        $this->addNPC(new MysteriousMerchant());
        $this->addNPC(new Alchemist());
        $this->addNPC(new Auctioneer());
        $this->addNPC(new Tinker());
        $this->addNPC(new Updater());
        $this->addNPC(new QuestGiver());
        $this->addNPC(new Enchanter());
        if (date("l") === "Friday") {
            $this->addNPC(new Voldemort());
        }
    }

    /**
     * @return NPC[]
     */
    public function getNPCs(): array {
        return $this->npcs;
    }

    /**
     * @param int $entityId
     *
     * @return NPC|null
     */
    public function getNPC(int $entityId): ?NPC {
        return $this->npcs[$entityId] ?? null;
    }

    /**
     * @param NPC $npc
     */
    public function addNPC(NPC $npc): void {
        $this->npcs[$npc->getEntityId()] = $npc;
    }

    /**
     * @param NPC $npc
     */
    public function removeNPC(NPC $npc): void {
        unset($this->npcs[$npc->getEntityId()]);
    }
}