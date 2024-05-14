<?php

declare(strict_types=1);

namespace Xekvern\Core\Player;

use Xekvern\Core\Provider\Task\LoadScreenTask;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Session\Types\CESession;
use Xekvern\Core\Session\Types\DataSession;
use Xekvern\Core\Utils\Utils;
use muqsit\invmenu\InvMenu;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\form\Form;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\permission\Permission;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\sound\XpLevelUpSound;
use Xekvern\Core\Nexus;
use Xekvern\Core\NexusException;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Server\Update\Utils\Scoreboard;
use Xekvern\Core\Utils\FloatingTextParticle;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\PlayerFogPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use Xekvern\Core\Player\Faction\FactionHandler;
use Xekvern\Core\Server\Item\ItemHandler;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use Xekvern\Core\Server\Item\Types\Drops;
use Xekvern\Core\Server\Update\Utils\BossBar;

class NexusPlayer extends Player
{

    const PUBLIC = 0;
    const FACTION = 1;
    const ALLY = 2;
    const STAFF = 3;

    /** @var int*/
    public $cps = 0;

    /** @var int */
    private $loadTime = 0;

    /** @var bool */
    private $transferred = false;

    /** @var null|CommandSender */
    private $lastTalked = null;

    /** @var null|string */
    private $lastHit = null;

    /** @var bool */
    private $vanish = false;

    /** @var bool */
    private $disguise = false;

    /** @var null|Rank */
    private $disguiseRank = null;

    /** @var Nexus */
    private $core;

    /** @var bool */
    private $runningCrateAnimation = false;

    /** @var bool */
    private $autoSell = false;

    /** @var bool */
    private $voteChecking = false;

    /** @var bool */
    private $voted = false;

    /** @var bool */
    private $teleporting = false;

    /** @var bool */
    private $frozen = false;

    /** @var bool */
    private $togglePrivateMessage = true;
    
    /** @var int */
    private $chatMode = self::PUBLIC;

    /** @var int */
    private $combatTag = 0;

    /** @var bool */
    private $combatTagged = false;

    private $luckyBlockToggle = true;

    /** @var Scoreboard */
    private $scoreboard;

    /** @var BossBar */
    private $bossBar;

    /** @var bool */
    private $fMapHud = false;

    /** @var FloatingTextParticle[] */
    private $floatingTexts = [];

    /** @var int[] */
    private $teleportRequests = [];

    /** @var string */
    private $os = "Unknown";

    /** @var null|DataSession */
    private $dataSession = null;

    /** @var null|CESession */
    private $ceSession = null;

    /** @var int */
    private $lastRepair = 0;

    /** @var int */
    private $lastBless = 0;

    private $lastSpeedAbilityScroll = 0;

    private $lastRegenAbilityScroll = 0;

    private $lastStrengthAbilityScroll = 0;

    private $lastResistAbilityScroll = 0;

    private $lastHasteAbilityScroll = 0;

    /** @var int[] */
    private $itemCooldowns = [];
    
    /** @var bool $staffMode */
    protected $staffMode = false;

    /** @var Item[] $staffModeInventory */
    protected $staffModeInventory = [];

    /** @var bool */
    private $breaking = false;

    /**
     * @param Nexus $core
     */
    public function load(Nexus $core): void
    {
        $this->core = $core;
        $this->loadTime = time();
        Nexus::getInstance()->getScheduler()->scheduleRepeatingTask(new LoadScreenTask($this), 1);
        $this->scoreboard = new Scoreboard($this);
        $this->bossBar = new BossBar($this);
        $this->dataSession = new DataSession($this);
        $this->ceSession = $core->getSessionManager()->getCESession($this);
    }

    /**
     * @return bool
     */
    public function justLoaded(): bool
    {
        return (time() - $this->loadTime) <= 30;
    }

    /**
     * @param CommandSender $sender
     */
    public function setLastTalked(CommandSender $sender): void
    {
        $this->lastTalked = $sender;
    }

    /**
     * @return CommandSender|null
     */
    public function getLastTalked(): ?CommandSender
    {
        if ($this->lastTalked === null) {
            return null;
        }
        if (!$this->lastTalked instanceof NexusPlayer) {
            return null;
        }
        return $this->lastTalked->isOnline() ? $this->lastTalked : null;
    }

    /**
     * @param string $name
     */
    public function setLastHit(?string $name): void
    {
        $this->lastHit = $name;
    }

    /**
     * @return string|null
     */
    public function getLastHit(): ?string
    {
        if ($this->lastHit === null) {
            return "None";
        }
        return $this->lastHit;
    }

    /**
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->ceSession !== null and $this->dataSession !== null and $this->dataSession->isLoaded();
    }

    /**
     * @return DataSession|null
     */
    public function getDataSession(): ?DataSession
    {
        return $this->dataSession;
    }

    /**
     * @return CESession|null
     */
    public function getCESession(): ?CESession
    {
        return $this->ceSession;
    }

    public function getLuckyBlockToggle(): bool {
        return $this->luckyBlockToggle;
    }

    public function setLuckyBlockToggle(bool $value): bool {
        return $this->luckyBlockToggle = $value;
    }

    /**
     * @param bool $value
     */
    public function vanish(bool $value = true): void
    {
        if ($value) {
            /** @var NexusPlayer $player */
            foreach ($this->getServer()->getOnlinePlayers() as $player) {
                if ($player->isLoaded() === false) {
                    continue;
                }
                if ($player->getDataSession()->getRank()->getIdentifier() >= Rank::TRIAL_MODERATOR and $player->getDataSession()->getRank()->getIdentifier() <= Rank::OWNER) {
                    continue;
                }
                $player->hidePlayer($this);
            }
        } else {
            foreach ($this->getServer()->getOnlinePlayers() as $player) {
                if (!$player->canSee($this)) {
                    $player->showPlayer($this);
                }
            }
        }
        $this->vanish = $value;
    }

    /**
     * @return bool
     */
    public function hasVanished(): bool
    {
        return $this->vanish;
    }

    /**
     * @return Nexus
     */
    public function getCore(): Nexus
    {
        return $this->core;
    }

    /**
     * @return Scoreboard
     */
    public function getScoreboard(): Scoreboard
    {
        return $this->scoreboard;
    }

    /**
     * @return BossBar
     */
    public function getBossBar(): BossBar {
        return $this->bossBar;
    }

    public function initializeScoreboard(): void
    {
        $this->scoreboard->spawn(Nexus::SERVER_NAME);
        $this->scoreboard->setScoreLine(1, " ");
        $this->scoreboard->setScoreLine(2, " " . $this->dataSession->getRank()->getColoredName() . TextFormat::RESET . TextFormat::WHITE . " " . $this->getName());
        $this->scoreboard->setScoreLine(3, " ");
        $this->scoreboard->setScoreLine(4, TextFormat::BOLD . TextFormat::AQUA . " STATS");
        $this->scoreboard->setScoreLine(5, TextFormat::RESET . TextFormat::YELLOW . "   K/D: " . TextFormat::RESET . TextFormat::WHITE . $this->dataSession->getKills() . ":" . $this->dataSession->getDeaths());
        $this->scoreboard->setScoreLine(6, TextFormat::RESET . TextFormat::YELLOW . "   Balance: " . TextFormat::RESET . TextFormat::WHITE . "$" . $this->dataSession->getBalance());
        $this->scoreboard->setScoreLine(7, TextFormat::RESET . TextFormat::YELLOW . "   Power: " . TextFormat::RESET . TextFormat::WHITE . $this->dataSession->getPower());
        $this->scoreboard->setScoreLine(8, TextFormat::RESET . TextFormat::YELLOW . "   Lucky Blocks: " . TextFormat::RESET . TextFormat::WHITE . $this->dataSession->getLuckyBlocksMined());
        $this->scoreboard->setScoreLine(9, " ");
        $this->scoreboard->setScoreLine(10, TextFormat::RESET . TextFormat::LIGHT_PURPLE . " >> store.nexuspe.net");
        $this->scoreboard->setScoreLine(11, TextFormat::RESET . TextFormat::LIGHT_PURPLE . " >> vote.nexuspe.net");
    }

    //public function initializeScoreboardA(): void
    //{
        //$this->scoreboard->spawn(Nexus::SERVER_NAME);
        //if($this->scoreboardDataExist(0)){$this->scoreboard->setScoreLine(1, $this->scoreboardDataFill(0));}else{if($this->scoreboard->getLine(1) !== null) {$this->scoreboard->removeLine(1);}}
        //if($this->scoreboardDataExist(1)){$this->scoreboard->setScoreLine(2, $this->scoreboardDataFill(1));}else{if($this->scoreboard->getLine(2) !== null) {$this->scoreboard->removeLine(2);}}
        //if($this->scoreboardDataExist(2)){$this->scoreboard->setScoreLine(3, $this->scoreboardDataFill(2));}else{if($this->scoreboard->getLine(3) !== null) {$this->scoreboard->removeLine(3);}}
        //if($this->scoreboardDataExist(3)){$this->scoreboard->setScoreLine(4, $this->scoreboardDataFill(3));}else{if($this->scoreboard->getLine(4) !== null) {$this->scoreboard->removeLine(4);}}
        //if($this->scoreboardDataExist(4)){$this->scoreboard->setScoreLine(5, $this->scoreboardDataFill(4));}else{if($this->scoreboard->getLine(5) !== null) {$this->scoreboard->removeLine(5);}}
        //if($this->scoreboardDataExist(5)){$this->scoreboard->setScoreLine(6, $this->scoreboardDataFill(5));}else{if($this->scoreboard->getLine(6) !== null) {$this->scoreboard->removeLine(6);}}
        //if($this->scoreboardDataExist(6)){$this->scoreboard->setScoreLine(7, $this->scoreboardDataFill(6));}else{if($this->scoreboard->getLine(7) !== null) {$this->scoreboard->removeLine(7);}}
        //if($this->scoreboardDataExist(7)){$this->scoreboard->setScoreLine(8, $this->scoreboardDataFill(7));}else{if($this->scoreboard->getLine(8) !== null) {$this->scoreboard->removeLine(8);}}
        //if($this->scoreboardDataExist(8)){$this->scoreboard->setScoreLine(9, $this->scoreboardDataFill(8));}else{if($this->scoreboard->getLine(9) !== null) {$this->scoreboard->removeLine(9);}}
        //if($this->scoreboardDataExist(9)){$this->scoreboard->setScoreLine(10, $this->scoreboardDataFill(9));}else{if($this->scoreboard->getLine(10) !== null) {$this->scoreboard->removeLine(10);}}
        //if($this->scoreboardDataExist(10)){$this->scoreboard->setScoreLine(11, $this->scoreboardDataFill(10));}else{if($this->scoreboard->getLine(11) !== null) {$this->scoreboard->removeLine(11);}}
        //if($this->scoreboardDataExist(11)){$this->scoreboard->setScoreLine(12, $this->scoreboardDataFill(11));}else{if($this->scoreboard->getLine(12) !== null) {$this->scoreboard->removeLine(12);}}
        //if($this->scoreboardDataExist(12)){$this->scoreboard->setScoreLine(13, $this->scoreboardDataFill(12));}else{if($this->scoreboard->getLine(13) !== null) {$this->scoreboard->removeLine(13);}}
        //if($this->scoreboardDataExist(13)){$this->scoreboard->setScoreLine(14, $this->scoreboardDataFill(13));}else{if($this->scoreboard->getLine(14) !== null) {$this->scoreboard->removeLine(14);}}
        //if($this->scoreboardDataExist(14)){$this->scoreboard->setScoreLine(15, $this->scoreboardDataFill(14));}else{if($this->scoreboard->getLine(15) !== null) {$this->scoreboard->removeLine(15);}}
    //}

    public function scoreboardDataFill(int $line): string{
        $lineString = $this->getDataSession()->getCustomScoreboardLine($line);
        $replacements = [
            '{empty}' => '',
            '{player_username}' => $this->getName(),
            '{player_rank}' => $this->dataSession->getRank()->getColoredName(),
            '{player_balance}' => number_format($this->dataSession->getBalance()),
            '{player_power}' => $this->dataSession->getPower()
        ];
        $lineString = strtr($lineString, $replacements);
        return $lineString;
    }

    public function scoreboardDataExist(int $line): bool{
        if($this->getDataSession()->getCustomScoreboardLine($line) === ""){
            return false;
        }else{
            return true;
        }
    }

    /**
     * @return bool
     */
    public function isUsingFMapHUD(): bool {
        return $this->fMapHud;
    }

    /**
     * @throws UtilsException
     */
    
    public function toggleFMapHUD(): void {
        $this->fMapHud = !$this->fMapHud;
        if($this->fMapHud === false) {
            $this->initializeScoreboard();
        }
        else {
            $lines = FactionHandler::sendFactionMap($this);
            $scoreboard = $this->getScoreboard();
            $i = 4;
            foreach($lines as $line) {
                $scoreboard->setScoreLine($i++, " " . $line);
            }
        }
    }
    
    public function setPlayerTag(): void
    {
        
        $rankId = $this->getDataSession()->getRank()->getIdentifier();
        /** @var NexusPlayer $onlinePlayer */
        foreach ($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
            if ($rankId >= Rank::TRIAL_MODERATOR and $rankId <= Rank::OWNER) {
                break;
            }
            if ($onlinePlayer->hasVanished()) {
                $this->hidePlayer($onlinePlayer);
            }
        }
        $this->setNameTag($this->dataSession->getRank()->getTagFormatFor($this, [
            "faction_rank" => $this->getDataSession()->getFactionRoleToString(),
            "faction" => $this->dataSession->getFaction() instanceof Faction ? $this->dataSession->getFaction()->getName() : "",
            "kills" => $this->dataSession->getKills()
        ]));
    }

    /**
     * @param bool $disguise
     */
    public function setDisguise(bool $disguise): void
    {
        $this->disguise = $disguise;
    }

    /**
     * @return bool
     */
    public function isDisguise(): bool
    {
        return $this->disguise;
    }

    /**
     * @return Rank|null
     */
    public function getDisguiseRank(): ?Rank
    {
        return $this->disguiseRank;
    }

    /**
     * @param Rank|null $disguiseRank
     */
    public function setDisguiseRank(?Rank $disguiseRank): void
    {
        $this->disguiseRank = $disguiseRank;
        if ($this->disguiseRank !== null) {
            $this->setNameTag($this->disguiseRank->getTagFormatFor($this, [
                "faction_rank" => "",
                "faction" => "",
                "kills" => $this->getDataSession()->getKills()
            ]));
            return;
        }
        $this->setPlayerTag();
    }

    /**
     * @return bool
     */
    public function isAutoSelling(): bool
    {
        return $this->autoSell;
    }

    /**
     * @param bool $value
     */
    public function setAutoSelling(bool $value = true): void
    {
        $this->autoSell = $value;
    }

    /**
     * @param bool $value
     */
    public function setCheckingForVote(bool $value = true): void
    {
        $this->voteChecking = $value;
    }

    /**
     * @return bool
     */
    public function isCheckingForVote(): bool
    {
        return $this->voteChecking;
    }

    /**
     * @return bool
     */
    public function hasVoted(): bool
    {
        return $this->voted;
    }

    /**
     * @param bool $value
     */
    public function setVoted(bool $value = true): void
    {
        $this->voted = $value;
    }

    /**
     * @return bool
     */
    public function isRunningCrateAnimation(): bool
    {
        return $this->runningCrateAnimation;
    }

    /**
     * @param bool $value
     */
    public function setRunningCrateAnimation(bool $value = true): void
    {
        $this->runningCrateAnimation = $value;
    }

    /**
     * @return FloatingTextParticle[]
     */
    public function getFloatingTexts(): array
    {
        return $this->floatingTexts;
    }

    /**
     * @param string $identifier
     *
     * @return FloatingTextParticle|null
     */
    public function getFloatingText(string $identifier): ?FloatingTextParticle
    {
        return $this->floatingTexts[$identifier] ?? null;
    }

    /**
     * @param Position $position
     * @param string $identifier
     * @param string $message
     *
     * @throws NexusException
     */
    public function addFloatingText(Position $position, string $identifier, string $message): void
    {
        if ($position->getWorld() === null) {
            throw new NexusException("Attempt to add a floating text particle with an invalid world.");
        }
        $floatingText = new FloatingTextParticle($position, $identifier, $message);
        $this->floatingTexts[$identifier] = $floatingText;
        $floatingText->sendChangesTo($this);
    }

    /**
     * @param string $identifier
     *
     * @throws NexusException
     */
    public function removeFloatingText(string $identifier): void
    {
        $floatingText = $this->getFloatingText($identifier);
        if ($floatingText === null) {
            throw new NexusException("Failed to despawn floating text: $identifier");
        }
        $floatingText->despawn($this);
        unset($this->floatingTexts[$identifier]);
    }

    /**
     * @param Permission|string $name
     *
     * @return bool
     */
    public function hasPermission($name): bool
    {
        if ($this->isLoaded()) {
            if (in_array($name, $this->getDataSession()->getPermissions())) {
                return true;
            }
            if ($this->getDataSession()->getRank() !== null) {
                if (in_array($name, $this->getDataSession()->getRank()->getPermissions())) {
                    return true;
                }
            }
            if (in_array($name, $this->getDataSession()->getPermanentPermissions())) {
                return true;
            }
        }
        return parent::hasPermission($name);
    }

    /**
     * @param int $amount
     * @param bool $playSound
     *
     * @return bool
     */
    public function addXp(int $amount, bool $playSound = true): bool
    {
        if ($amount + $this->getXpManager()->getCurrentTotalXp() > 0x7fffffff) {
            return false;
        }
        $bool = $this->getXpManager()->addXp($amount, $playSound);
        return $bool;
    }

    /**
     * @param NexusPlayer $player
     *
     * @return bool
     */
    public function isRequestingTeleport(NexusPlayer $player): bool
    {
        return isset($this->teleportRequests[$player->getUniqueId()->toString()]) and (time() - $this->teleportRequests[$player->getUniqueId()->toString()]) < 30;
    }

    /**
     * @param NexusPlayer $player
     */
    public function addTeleportRequest(NexusPlayer $player): void
    {
        $this->teleportRequests[$player->getUniqueId()->toString()] = time();
    }

    /**
     * @param NexusPlayer $player
     */
    public function removeTeleportRequest(NexusPlayer $player): void
    {
        if (isset($this->teleportRequests[$player->getUniqueId()->toString()])) {
            unset($this->teleportRequests[$player->getUniqueId()->toString()]);
        }
    }

    /**
     * @return bool
     */
    public function isTeleporting(): bool
    {
        return $this->teleporting;
    }

    /**
     * @param bool $value
     */
    public function setTeleporting(bool $value = true): void
    {
        $this->teleporting = $value;
    }

    /**
     * @return bool
     */
    public function isFrozen(): bool 
    {
        return $this->frozen;
    }

    /**
     * @param bool $frozen
     */
    public function setFrozen(bool $frozen = true): void 
    {
        $this->frozen = $frozen;
    }

    /**
     * @return int
     */
    public function getLastRepair(): int {
        return $this->lastRepair;
    }

    public function setLastRepair(): void {
        $this->lastRepair = time();
    }

    /**
     * @return int
     */
    public function getLastBless(): int {
        return $this->lastBless;
    }

    public function setLastBless(): void {
        $this->lastBless = time();
    }

    /**
     * @return int
     */
    public function getLastSpeedAbilityScroll(): int {
        return $this->lastSpeedAbilityScroll;
    }

    public function setLastSpeedAbilityScroll(): void {
        $this->lastSpeedAbilityScroll = time();
    }

    /**
     * @return int
     */
    public function getLastRegenAbilityScroll(): int {
        return $this->lastRegenAbilityScroll;
    }

    public function setLastRegenAbilityScroll(): void {
        $this->lastRegenAbilityScroll = time();
    }

    /**
     * @return int
     */
    public function getLastStrengthAbilityScroll(): int {
        return $this->lastStrengthAbilityScroll;
    }

    public function setLastStrengthAbilityScroll(): void {
        $this->lastStrengthAbilityScroll = time();
    }

    /**
     * @return int
     */
    public function getLastResistAbilityScroll(): int {
        return $this->lastResistAbilityScroll;
    }

    public function setLastResistAbilityScroll(): void {
        $this->lastResistAbilityScroll = time();
    }

    /**
     * @return int
     */
    public function getLastHasteAbilityScroll(): int {
        return $this->lastHasteAbilityScroll;
    }

    public function setLastHasteAbilityScroll(): void {
        $this->lastHasteAbilityScroll = time();
    }

    /**
     * @param string $type
     */
    public function setCustomItemCooldown(string $type): void {
        $this->itemCooldowns[$type] = time();
    }

    /**
     * @param string $type
     *
     * @return int
     */
    public function getCustomItemCooldown(string $type): int {
        return $this->itemCooldowns[$type] ?? 0;
    }

    /**
     * @return int
     */
    public function getChatMode(): int
    {
        return $this->chatMode;
    }

    /**
     * @return string
     */
    public function getChatModeToString(): string
    {
        return match ($this->chatMode) {
            self::PUBLIC => "public",
            self::FACTION => "faction",
            self::ALLY => "ally",
            self::STAFF => "staff",
            default => "unknown",
        };
    }

    /**
     * @param int $mode
     */
    public function setChatMode(int $mode): void
    {
        $this->chatMode = $mode;
    }

    /**
     * @return string
     */
    public function getOS(): string
    {
        return $this->os;
    }

    /**
     * @param string $os
     */
    public function setOS(string $os): void
    {
        $this->os = $os;
    }


    /**
     * @param bool $value
     */
    public function combatTag(bool $value): void
    {
        if ($value) {
            $this->combatTag = 15;
        }
    }

    /**
     * @param int $value
     */
    public function setCombatTagTime(int $value): void
    {
        $this->combatTag = $value;
    }

    /**
     * @param int $value
     */
    public function setCombatTagged(bool $value): void
    {
        $this->combatTagged = $value;
    }

    /**
     * @return int
     */
    public function combatTagTime(): int
    {
        return $this->combatTag;
    }

    /**
     * @return bool
     */
    public function isTagged(): bool
    {
        return $this->combatTagged;
    }

    public function isInStaffMode(): bool
    {
        return $this->staffMode;
    }

    public function setStaffMode(bool $status =  true): void
    {
        $this->staffMode = $status;
        if ($status) {
            $item = (new Drops($this->getName(), array_merge($this->getInventory()->getContents(), $this->getArmorInventory()->getContents())));
            $this->getDataSession()->addToInbox($item->getItemForm());
           //$this->setStaffModeInventory($this->getInventory()->getContents());
            $this->getArmorInventory()->clearAll();
            $this->getInventory()->clearAll();
            $this->vanish(true);
            $this->setFlying(true);
            $this->setAllowFlight(true);
            $this->setHealth(20);
            $this->getHungerManager()->setFood(20);
            $this->getInventory()->setItem(0, VanillaBlocks::ICE()->asItem()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "Freeze / UnFreeze"));
            $this->getInventory()->setItem(3, VanillaItems::COMPASS()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "Choose Player to Teleport"));
            $this->getInventory()->setItem(5, VanillaItems::CLOCK()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Teleport To Random Player"));
            $this->getInventory()->setItem(8, VanillaBlocks::CHEST()->asItem()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "Check Inventory"));
            $pk = new GameRulesChangedPacket();
            $pk->gameRules = ["showcoordinates" => new BoolGameRule(false, false)];
            $this->getNetworkSession()->sendDataPacket($pk);
        } else {
            $this->getInventory()->clearAll();
            $this->sendMessage(TextFormat::BOLD . TextFormat::RED . "(Staff Mode) " . TextFormat::RESET . TextFormat::GRAY . "Your items are currently in your /inbox as a Drops item.");
            //$this->getInventory()->setContents($this->getStaffModeInventory());
            $this->vanish(false);
            $this->setHealth(20);
            $this->getHungerManager()->setFood(20);
            $this->setFlying(false);
            $this->setAllowFlight(false);
            $this->teleport($this->getCore()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        }
    }

    public function getStaffModeInventory(): array
    {
        return $this->staffModeInventory;
    }

    public function setStaffModeInventory(array $inventory): void
    {
        $this->staffModeInventory = $inventory;
    }

    /**
     * @return bool
     */
    public function isTakingPMs(): bool {
        return $this->togglePrivateMessage;
    }

    public function togglePMs(): void {
        $this->togglePrivateMessage = !$this->togglePrivateMessage;
    }

    public function sendDelayedWindow(InvMenu $menu): void
    {
        Nexus::getInstance()->getScheduler()->scheduleDelayedTask(new class($menu, $this) extends Task
        {

            /** @var InvMenu */
            private $menu;

            /** @var NexusPlayer */
            private $player;

            /**
             *  constructor.
             *
             * @param InvMenu $menu
             * @param NexusPlayer $player
             */
            public function __construct(InvMenu $menu, NexusPlayer $player)
            {
                $this->menu = $menu;
                $this->player = $player;
            }

            /**
             * @param int $currentTick
             */
            public function onRun(): void
            {
                if ($this->player->isOnline() and (!$this->player->isClosed())) {
                    $this->menu->send($this->player);
                }
            }
        }, 20);
    }

    public function sendDelayedForm(Form $form): void
    {
        $this->getCore()->getScheduler()->scheduleDelayedTask(new class($form, $this) extends Task
        {

            /** @var Form */
            private $form;

            /** @var NexusPlayer */
            private $player;

            /**
             *  constructor.
             *
             * @param Form $form
             * @param NexusPlayer $player
             */
            public function __construct(Form $form, NexusPlayer $player)
            {
                $this->form = $form;
                $this->player = $player;
            }

            /**
             * @param int $currentTick
             */
            public function onRun(): void
            {
                if ($this->player->isOnline() and (!$this->player->isClosed())) {
                    $this->player->sendForm($this->form);
                }
            }
        }, 20);
    }

    /**
     * @param float $pitch
     * @param int $volume
     */
    public function playOrbSound(float $pitch = 1.0, int $volume = 400): void {
        $pk = new PlaySoundPacket();
        $pk->soundName = "random.orb";
        $pk->x = $this->getPosition()->x;
        $pk->y = $this->getPosition()->y;
        $pk->z = $this->getPosition()->z;
        $pk->volume = $volume;
        $pk->pitch = $pitch;
        $this->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @param int $extraData
     */
    public function playNoteSound(int $extraData = 1): void
    {
        $sound = new NoteSound(NoteInstrument::PIANO(), 1);
        $this->broadcastSound($sound, [$this]);
    }

    public function playErrorSound(): void
    {
        $sound = new NoteSound(NoteInstrument::PLING(), 3);
        $this->broadcastSound($sound, [$this]);
    }

    /**
     * @param float $pitch
     */
    public function playXpLevelUpSound(float $pitch = 1.0): void
    {
        $this->getWorld()->addSound($this->getPosition(), new XpLevelUpSound(30));
    }

    /**
     * @param int $pitch
     */
    public function playDingSound(int $pitch = -1): void
    {
        if ($pitch !== -1) {
            $pitch *= 1000;
        } else {
            $pitch = 100000000;
        }
        $sound = new XpCollectSound();
        $this->broadcastSound($sound, [$this]);
    }

    public function playConsecutiveDingSound(): void {
        $this->getCore()->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {

            /** @var NexusPlayer */
            private $player;

            /** @var int */
            private $runs = 0;

            /**
             *  constructor.
             *
             * @param Form $form
             * @param NexusPlayer $player
             */
            public function __construct(NexusPlayer $player) {
                $this->player = $player;
            }

            /**
             * @param int $currentTick
             */
            public function onRun(): void {
                if(++$this->runs > 3) {
                    $this->getHandler()->cancel();
                    return;
                }
                if($this->player->isOnline() and (!$this->player->isClosed())) {
                    $this->player->playDingSound();
                }
            }
        }, 4);
    }

    /**
     * @param string $sound
     */
    public function playSound(string $sound, $volume = 1, $pitch = 1): void {
        $spk = new PlaySoundPacket();
        $spk->soundName = $sound;
        $spk->x = $this->getLocation()->getX();
        $spk->y = $this->getLocation()->getY();
        $spk->z = $this->getLocation()->getZ();
        $spk->volume = $volume;
        $spk->pitch = $pitch;
        $this->getNetworkSession()->sendDataPacket($spk);
	}

    /**
     * @param Vector3 $pos
     * @param float|null $yaw
     * @param float|null $pitch
     *
     * @return bool
     */
    public function teleport(Vector3 $pos, float $yaw = null, float $pitch = null): bool
    {
        if ($pos instanceof Position) {
            $world = $pos->getWorld();
            if ($world !== null) {
                if ($world->getDisplayName() === Nexus::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getDisplayName() or $world->getDisplayName() === Faction::CLAIM_WORLD) {
                    $this->getNetworkSession()->sendDataPacket(PlayerFogPacket::create(["minecraft:default"]));
                }
                if ($world->getDisplayName() == "warzone") {
                    $this->getNetworkSession()->sendDataPacket(PlayerFogPacket::create(["minecraft:fog_hell"]));
                }
                if ($world->getDisplayName() == "bossarena") {
                    $this->getNetworkSession()->sendDataPacket(PlayerFogPacket::create(["minecraft:fog_the_end"]));
                }                
            }
        }
        return parent::teleport($pos, $yaw, $pitch);
    }
}
