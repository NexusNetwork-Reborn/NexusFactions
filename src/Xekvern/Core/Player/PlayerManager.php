<?php

namespace Xekvern\Core\Player;

use Exception;
use pocketmine\entity\Entity;
use Xekvern\Core\Player\Combat\CombatHandler;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Vault\VaultHandler;
use Xekvern\Core\Player\Faction\FactionHandler;
use Xekvern\Core\Player\Gamble\GambleHandler;
use Xekvern\Core\Player\Rank\RankHandler;
use Xekvern\Core\Player\PlayerEvents;
use Xekvern\Core\Player\Quest\QuestHandler;

class PlayerManager
{

    /** @var Nexus */
    private $core;

    /** @var FactionHandler */
    private $factionHandler;

    /** @var RankHandler */
    private $rankHandler;

    /** @var CombatHandler */
    private $combatHandler;

    /** @var GambleHandler */
    private $gambleHandler;

    /** @var VaultHandler */
    private $vaultHandler;

    /** @var QuestHandler */
    private $questHandler;

    /**
     * PlayerManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
        $core->getServer()->getPluginManager()->registerEvents(new PlayerEvents($core), $core);
        $this->initiateHandlers();
    }

    /**
     * @return RankHandler
     */
    public function getRankHandler(): RankHandler
    {
        return $this->rankHandler;
    }

    /**
     * @return FactionHandler
     */
    public function getFactionHandler(): FactionHandler
    {
        return $this->factionHandler;
    }

    /**
     * @return CombatHandler
     */
    public function getCombatHandler(): CombatHandler
    {
        return $this->combatHandler;
    }

    /**
     * @return GambleHandler
     */
    public function getGambleHandler(): GambleHandler
    {
        return $this->gambleHandler;
    }

    /**
     * @return VaultHandler
     */
    public function getVaultHandler(): VaultHandler 
    {
        return $this->vaultHandler;
    }

    /**
     * @return QuestHandler
     */
    public function getQuestHandler(): QuestHandler 
    {
        return $this->questHandler;
    }

    /** 
     * @return bool
     */
    public function initiateHandlers(): bool
    {
        $this->factionHandler = new FactionHandler($this->core);
        $this->rankHandler = new RankHandler($this->core);
        $this->combatHandler = new CombatHandler($this->core);
        $this->vaultHandler = new VaultHandler($this->core);
        $this->gambleHandler = new GambleHandler($this->core);
        $this->questHandler = new QuestHandler($this->core);
        return true;
    }
}
