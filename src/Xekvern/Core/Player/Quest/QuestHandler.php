<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Quest;

use pocketmine\block\BlockTypeIds;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Quest\Types\BreakQuest;
use Xekvern\Core\Player\Quest\Types\BuyQuest;
use Xekvern\Core\Player\Quest\Types\KillQuest;
use Xekvern\Core\Player\Quest\Types\PlaceQuest;
use Xekvern\Core\Player\Quest\Types\SellQuest;

class QuestHandler {

    /** @var Session[] */
    private array $sessions = [];

    /** @var Quest[] */
    private array $quests = [];

    /** @var Quest[] */
    private array $activeQuest = [];

    public function __construct(protected Nexus $core) {
        $this->init();
        while (count($this->activeQuest) < 3) {
            $this->activeQuest[] = $this->quests[array_rand($this->quests)];
        }
        $core->getServer()->getPluginManager()->registerEvents(new QuestEvents($core), $core);
    }

    /**
     * @throws QuestException
     */
    public function init(): void {
        $this->addQuest(new BreakQuest("Noob Coal Miner", "Mine 50 coal ores.", 50, Quest::EASY, BlockTypeIds::COAL_ORE));
        $this->addQuest(new BreakQuest("Amateur Coal Miner", "Mine 100 coal ores.", 100, Quest::MEDIUM, BlockTypeIds::COAL_ORE));
        $this->addQuest(new BreakQuest("Pro Coal Miner", "Mine 250 coal ores.", 250, Quest::HARD, BlockTypeIds::COAL_ORE));
        $this->addQuest(new BreakQuest("Noob Redstone Miner", "Mine 50 redstone ores.", 50, Quest::EASY, BlockTypeIds::REDSTONE_ORE));
        $this->addQuest(new BreakQuest("Amateur Redstone Miner", "Mine 100 redstone ores.", 100, Quest::MEDIUM, BlockTypeIds::REDSTONE_ORE));
        $this->addQuest(new BreakQuest("Pro Redstone Miner", "Mine 250 redstone ores.", 250, Quest::HARD, BlockTypeIds::REDSTONE_ORE));
        $this->addQuest(new BreakQuest("Noob Iron Miner", "Mine 50 iron ores.", 50, Quest::EASY, BlockTypeIds::IRON_ORE));
        $this->addQuest(new BreakQuest("Amateur Iron Miner", "Mine 100 iron ores.", 100, Quest::MEDIUM, BlockTypeIds::IRON_ORE));
        $this->addQuest(new BreakQuest("Pro Iron Miner", "Mine 250 iron ores.", 250, Quest::HARD, BlockTypeIds::IRON_ORE));
        $this->addQuest(new BreakQuest("Noob Gold Miner", "Mine 50 gold ores.", 50, Quest::EASY, BlockTypeIds::GOLD_ORE));
        $this->addQuest(new BreakQuest("Amateur Gold Miner", "Mine 100 gold ores.", 100, Quest::MEDIUM, BlockTypeIds::GOLD_ORE));
        $this->addQuest(new BreakQuest("Pro Gold Miner", "Mine 250 gold ores.", 250, Quest::HARD, BlockTypeIds::GOLD_ORE));
        $this->addQuest(new BreakQuest("Noob Diamond Miner", "Mine 50 diamond ores.", 50, Quest::EASY, BlockTypeIds::DIAMOND_ORE));
        $this->addQuest(new BreakQuest("Amateur Diamond Miner", "Mine 100 diamond ores.", 100, Quest::MEDIUM, BlockTypeIds::DIAMOND_ORE));
        $this->addQuest(new BreakQuest("Pro Diamond Miner", "Mine 250 diamond ores.", 250, Quest::HARD, BlockTypeIds::DIAMOND_ORE));
        $this->addQuest(new BreakQuest("Noob Emerald Miner", "Mine 50 emerald ores.", 50, Quest::EASY, BlockTypeIds::EMERALD_ORE));
        $this->addQuest(new BreakQuest("Amateur Emerald Miner", "Mine 100 emerald ores.", 100, Quest::MEDIUM, BlockTypeIds::EMERALD_ORE));
        $this->addQuest(new BreakQuest("Pro Emerald Miner", "Mine 250 emerald ores.", 250, Quest::HARD, BlockTypeIds::EMERALD_ORE));
        $this->addQuest(new PlaceQuest("Noob Builder", "Place 50 cobblestone.", 50, Quest::EASY, BlockTypeIds::COBBLESTONE));
        $this->addQuest(new PlaceQuest("Amateur Builder", "Place 50 obsidian.", 50, Quest::MEDIUM, BlockTypeIds::OBSIDIAN));
        $this->addQuest(new PlaceQuest("Pro Builder", "Place 50 bedrock.", 50, Quest::HARD, BlockTypeIds::BEDROCK));
        $this->addQuest(new KillQuest("Murderer", "Kill 2 players.", 2, Quest::EASY));
        $this->addQuest(new KillQuest("Serial Killer", "Kill 5 players.", 5, Quest::MEDIUM));
        $this->addQuest(new KillQuest("Assassin", "Kill 10 players.", 10, Quest::HARD));
        $this->addQuest(new SellQuest("Noob Vendor", "Sell $1,000 worth of items.", 1000, Quest::EASY));
        $this->addQuest(new SellQuest("Amateur Vendor", "Sell $10,000 worth of items.", 10000, Quest::MEDIUM));
        $this->addQuest(new SellQuest("Pro Vendor", "Sell $100,000 worth of items.", 100000, Quest::HARD));
        $this->addQuest(new BuyQuest("Noob Spender", "Buy $10,000 worth of items.", 10000, Quest::EASY));
        $this->addQuest(new BuyQuest("Amateur Spender", "Sell $100,000 worth of items.", 100000, Quest::MEDIUM));
        $this->addQuest(new BuyQuest("Pro Spender", "Sell $1,000,000 worth of items.", 1000000, Quest::HARD));

    }

    /**
     * @param Quest $quest
     * @throws QuestException
     */
    public function addQuest(Quest $quest): void {
        if (isset($this->quests[$quest->getName()])) {
            throw new QuestException("Attempt to override an existing quest named: " . $quest->getName());
        }
        $this->quests[$quest->getName()] = $quest;
    }

    /**
     * @param NexusPlayer $player
     * @return Session
     */
    public function getSession(NexusPlayer $player): Session {
        if (!isset($this->sessions[$player->getUniqueId()->toString()])) {
            $this->addSession($player);
        }
        return $this->sessions[$player->getUniqueId()->toString()];
    }

    /**
     * @param NexusPlayer $player
     */
    public function addSession(NexusPlayer $player): void {
        if (!isset($this->sessions[$player->getUniqueId()->toString()])) {
            $this->sessions[$player->getUniqueId()->toString()] = new Session($player);
        }
    }

    /**
     * @return Quest[]
     */
    public function getQuests(): array {
        return $this->quests;
    }

    /**
     * @param string $name
     * @return Quest|null
     */
    public function getQuest(string $name): ?Quest {
        return $this->quests[$name] ?? null;
    }

    /**
     * @return Quest[]
     */
    public function getActiveQuests(): array {
        return $this->activeQuest;
    }
}