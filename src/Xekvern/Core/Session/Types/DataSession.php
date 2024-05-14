<?php

namespace Xekvern\Core\Session\Types;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Utils\Utils;
use muqsit\invmenu\{
    InvMenu,
    transaction\InvMenuTransaction,
    transaction\InvMenuTransactionResult,
    inventory\InvMenuInventory,
};
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Player\Vault\Vault;
use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Server\Kit\Kit;
use Xekvern\Core\Server\Kit\SacredKit;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Server\World\WorldHandler;
use Xekvern\Core\Server\World\Utils\GeneratorId;

class DataSession
{

    /** @var NexusPlayer */
    private $owner;

    /** @var Nexus */
    private $core;

    /** @var int */
    private $successCount = 0;

    /** @var int */
    private $loadTime = 0;

    /** @var int[] */
    private $crates = [];

    /** @var int */
    private $balance = 0;

    /** @var int */
    private $bounty = 0;

    /** @var int */
    private $kills = 0;

    /** @var int */
    private $deaths = 0;

    /** @var int */
    private $power = 0;

    /** @var int */
    private $luckyBlocks = 0;

    /** @var int */
    private $votePoints = 0;

    /** @var int */
    private $gems = 0;

    /** @var int */
    private $questPoints = 0;

    /** @var Rank */
    private $rank;

    /** @var string[] */
    private $permissions = [];

    /** @var string[] */
    private $permanentPermissions = [];

    /** @var string[] */
    private $tags = [];

    /** @var null|string */
    private $currentTag = null;

    /** @var null|Faction */
    private $faction = null;

    /** @var null|int */
    private $factionRole = null;

    /** @var InvMenu */
    private $inbox;

    /** @var Position[] */
    private $homes = [];

    /** @var int */
    private $kitCooldowns = [];

    /** @var int */
    private $sacredKitTiers = [];

    /** @var int */
    private $onlineTime = 0;

    /** @var int */
    private $sellWandUses = 0;

    /** @var array */
    private $scoreboardCustomLines = [];

    /**
     * DataSession constructor.
     *
     * @param NexusPlayer $owner
     */
    public function __construct(NexusPlayer $owner)
    {
        $this->owner = $owner;
        $this->core = Nexus::getInstance();
        $this->load();
    }

    /**
     * @return NexusPlayer
     */
    public function getOwner(): NexusPlayer
    {
        return $this->owner;
    }

    /**
     * @param NexusPlayer $owner
     */
    public function setOwner(NexusPlayer $owner): void
    {
        $this->owner = $owner;
    }

    public function load(): void
    {
        $this->inbox = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $this->inbox->setName(TextFormat::YELLOW . "Inbox");
        $this->inbox->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            $itemClickedWith = $transaction->getItemClickedWith();
            $itemClicked = $transaction->getItemClicked();
            if ($itemClickedWith->getTypeId() !== VanillaItems::AIR()->getTypeId()) {
                return $transaction->discard();
            }
            if ($itemClicked->getTypeId() === VanillaItems::AIR()->getTypeId()) {
                return $transaction->discard();
            }
            return $transaction->continue();
        });
        $this->inbox->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory): void {
            $uuid = $player->getUniqueId()->toString();
            $items = Nexus::encodeInventory($inventory);
            $stmt = $this->core->getMySQLProvider()->getDatabase()->prepare("UPDATE users SET inbox = ? WHERE uuid = ?");
            $stmt->bind_param("ss", $items, $uuid);
            $stmt->execute();
            $stmt->close();
        });
        $uuid = $this->owner->getUniqueId()->toString();
        $connector = Nexus::getInstance()->getMySQLProvider()->getConnector();
        $connector->executeSelect("SELECT faction, factionRole, kills, deaths, luckyBlocks, balance, bounty, power, questPoints, sellWandUses, onlineTime, kitCooldowns, kitTiers, crateKeys FROM stats WHERE uuid = ?", "s", [
            $uuid
        ], function (array $rows) {
            $start = microtime(true);
            foreach ($rows as [
                "faction" => $faction,
                "factionRole" => $factionRole,
                "kills" => $kills,
                "deaths" => $deaths,
                "luckyBlocks" => $luckyBlocks,
                "balance" => $balance,
                "bounty" => $bounty,
                "power" => $power,
                "questPoints" => $questPoints,
                "sellWandUses" => $sellWandUses,
                "onlineTime" => $onlineTime,
                "kitCooldowns" => $kitCooldowns,
                "kitTiers" => $kitTiers,
                "crateKeys" => $crateKeys
            ]) {
                $this->faction = $faction !== null ? $this->core->getPlayerManager()->getFactionHandler()->getFaction($faction) : null;
                $this->factionRole = $faction !== null ? $factionRole : null;
                $this->kills = $kills;
                $this->deaths = $deaths;
                $this->luckyBlocks = $luckyBlocks;
                $this->balance = $balance;
                $this->bounty = $bounty;
                $this->power = $power;
                $this->questPoints = $questPoints;
                $this->sellWandUses = $sellWandUses;
                $this->onlineTime = $onlineTime;
                $this->kitCooldowns = Utils::decodeArray($kitCooldowns);
                $this->sacredKitTiers = Utils::decodeArray($kitTiers);
                $this->crates = Utils::decodeArray($crateKeys);
            }
            $this->successCount++;
            $time = (microtime(true) - $start);
            $this->core->getLogger()->notice("[Load 1] Completed in " . ($time >= 1 ? round($time, 3) . "s" : round($time * 1000) . "ms"));
        });
        $connector->executeSelect("SELECT rankId, permissions, permanentPermissions, votePoints, gems, tags, currentTag, inbox FROM users WHERE uuid = ?", "s", [
            $uuid
        ], function (array $rows) {
            $start = microtime(true);
            if (empty($rows)) {
                $this->rank = $this->core->getPlayerManager()->getRankHandler()->getRankByIdentifier(Rank::PLAYER);
            } else {
                foreach ($rows as [
                    "rankId" => $rankId,
                    "permissions" => $permissions,
                    "permanentPermissions" => $permanentPermissions,
                    "votePoints" => $votePoints,
                    "gems" => $gems,
                    "tags" => $tags,
                    "currentTag" => $currentTag,
                    "inbox" => $inbox
                ]) {
                    $this->rank = $this->core->getPlayerManager()->getRankHandler()->getRankByIdentifier($rankId);
                    $this->permissions = Utils::decodeArray($permissions);
                    $this->permanentPermissions = Utils::decodeArray($permanentPermissions);
                    $this->votePoints = $votePoints;
                    $this->gems = $gems;
                    $this->tags = Utils::decodeArray($tags);
                    $this->currentTag = $currentTag;
                    $items = Nexus::decodeInventory($inbox);
                    $i = 0;
                    foreach ($items as $item) {
                        $this->inbox->getInventory()->setItem($i, $item);
                        ++$i;
                    }
                }
            }
            $rankId = $this->rank->getIdentifier();
            $this->owner->setPlayerTag();
            /** @var NexusPlayer $onlinePlayer */
            foreach ($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
                if ($rankId >= Rank::TRIAL_MODERATOR and $rankId <= Rank::OWNER) {
                    break;
                }
                if ($onlinePlayer->hasVanished()) {
                    $this->owner->hidePlayer($onlinePlayer);
                }
            }
            $this->successCount++;
            $time = (microtime(true) - $start);
            $this->core->getLogger()->notice("[Load 2] Completed in " . ($time >= 1 ? round($time, 3) . "s" : round($time * 1000) . "ms"));
        });
        $connector->executeSelect("SELECT name, x, y, z, level FROM homes WHERE uuid = ?;", "s", [
            $uuid
        ], function (array $rows) {
            $start = microtime(true);
            foreach ($rows as [
                "name" => $name,
                "x" => $x,
                "y" => $y,
                "z" => $z,
                "level" => $level
            ]) {
                $lvl = $this->owner->getServer()->getWorldManager()->getWorldByName($level);
                if ($lvl === null) {
                    continue;
                }
                $this->homes[$name] = new Position($x, $y, $z, $lvl);
            }
            ++$this->successCount;
            $time = (microtime(true) - $start);
            $this->core->getLogger()->notice("[Load 3] Completed in " . ($time >= 1 ? round($time, 3) . "s" : round($time * 1000) . "ms"));
        });
        $connector->executeSelect("SELECT line1, line2, line3, line4, line5, line6, line7, line8, line9, line10, line11, line12, line13, line14, line15 FROM scoreboardCustomisation WHERE uuid = ?;", "s", [
            $uuid
        ], function (array $rows) {
            $start = microtime(true);
            foreach ($rows as [
                "line1" => $line1,
                "line2" => $line2,
                "line3" => $line3,
                "line4" => $line4,
                "line5" => $line5,
                "line6" => $line6,
                "line7" => $line7,
                "line8" => $line8,
                "line9" => $line9,
                "line10" => $line10,
                "line11" => $line11,
                "line12" => $line12,
                "line13" => $line13,
                "line14" => $line14,
                "line15" => $line15
            ]) {
                $this->scoreboardCustomLines = [$line1, $line2, $line3, $line4, $line5, $line6, $line7, $line8, $line9, $line10, $line11, $line12, $line13, $line14, $line15];
            }
            ++$this->successCount;
            $time = (microtime(true) - $start);
            $this->core->getLogger()->notice("[Load 4] Completed in " . ($time >= 1 ? round($time, 3) . "s" : round($time * 1000) . "ms"));
        });
        $username = $this->owner->getName();
        $connector->executeUpdate("INSERT IGNORE INTO scoreboardCustomisation(uuid, username, line1, line2, line3, line4, line5, line6, line7, line8, line9, line10, line11, line12, line13, line14, line15) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", "sssssssssssssssss", [$uuid, $username, $this->getCustomScoreboardLine(0), $this->getCustomScoreboardLine(1), $this->getCustomScoreboardLine(2), $this->getCustomScoreboardLine(3), $this->getCustomScoreboardLine(4), $this->getCustomScoreboardLine(5), $this->getCustomScoreboardLine(6), $this->getCustomScoreboardLine(7), $this->getCustomScoreboardLine(8), $this->getCustomScoreboardLine(9), $this->getCustomScoreboardLine(10), $this->getCustomScoreboardLine(11), $this->getCustomScoreboardLine(12), $this->getCustomScoreboardLine(13), $this->getCustomScoreboardLine(14)]);
    }

    /**
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->successCount === 4;
    }

    public function saveData(): void
    {
        if (!$this->isLoaded()) {
            return;
        }
        $uuid = $this->owner->getUniqueId()->toString();
        $username = $this->owner->getName();
        $permissions = Utils::encodeArray($this->permissions);
        $permanentPermissions = Utils::encodeArray($this->permanentPermissions);
        $rank = $this->rank->getIdentifier();
        $tags = Utils::encodeArray($this->tags);
        $inbox = Nexus::encodeInventory($this->inbox->getInventory());
        $database = $this->core->getMySQLProvider()->getDatabase();
        $stmt = $database->prepare("REPLACE INTO users(uuid, username, rankId, permissions, permanentPermissions, votePoints, gems, tags, currentTag, inbox) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssississss", $uuid, $username, $rank, $permissions, $permanentPermissions, $this->votePoints, $this->gems, $tags, $this->currentTag, $inbox);
        $stmt->execute();
        $stmt->close();
        $faction = $this->faction !== null ? $this->faction->getName() : null;
        $kitCooldowns = Utils::encodeArray($this->kitCooldowns);
        $kitTiers = Utils::encodeArray($this->sacredKitTiers);
        $crateKeys = Utils::encodeArray($this->crates);
        $stmt = $database->prepare("REPLACE INTO stats(uuid, username, faction, factionRole, kills, deaths, sellWandUses, luckyBlocks, balance, bounty, power, questPoints, onlineTime, kitCooldowns, kitTiers, crateKeys) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiiiiiissssss", $uuid, $username, $faction, $this->factionRole, $this->kills, $this->deaths, $this->sellWandUses, $this->luckyBlocks, $this->balance, $this->bounty, $this->power, $this->questPoints, $this->onlineTime, $kitCooldowns, $kitTiers, $crateKeys);
        $stmt->execute();
        $stmt->close();
    }

    public function saveDataAsync(): void
    {
        if (!$this->isLoaded()) {
            return;
        }
        if ((time() - $this->loadTime) < 60) {
            return;
        }
        $uuid = $this->owner->getUniqueId()->toString();
        $username = $this->owner->getName();
        $permissions = Utils::encodeArray($this->permissions);
        $permanentPermissions = Utils::encodeArray($this->permanentPermissions);
        $tags = Utils::encodeArray($this->tags);
        $inbox = Nexus::encodeInventory($this->inbox->getInventory());
        $rank = $this->rank->getIdentifier();
        $connnector = $this->core->getMySQLProvider()->getConnector();
        $connnector->executeUpdate("REPLACE INTO users(uuid, username, rankId, permissions, permanentPermissions, votePoints, gems, tags, currentTag, inbox) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", "ssississss", [$uuid, $username, $rank, $permissions, $permanentPermissions, $this->votePoints, $this->gems, $tags, $this->currentTag, $inbox]);
        $kitCooldowns = Utils::encodeArray($this->kitCooldowns);
        $faction = $this->faction !== null ? $this->faction->getName() : null;
        $kitTiers = Utils::encodeArray($this->sacredKitTiers);
        $crateKeys = Utils::encodeArray($this->crates);
        $connnector->executeUpdate("REPLACE INTO stats(uuid, username, faction, factionRole, kills, deaths, sellWandUses, luckyBlocks, balance, bounty, power, questPoints, onlineTime, kitCooldowns, kitTiers, crateKeys) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", "sssiiiiiiissssss", [$uuid, $username, $faction, $this->factionRole, $this->kills, $this->deaths, $this->sellWandUses, $this->luckyBlocks, $this->balance, $this->bounty, $this->power, $this->questPoints, $this->onlineTime, $kitCooldowns, $kitTiers, $crateKeys]);
        $connnector->executeUpdate("REPLACE INTO scoreboardCustomisation(uuid, username, line1, line2, line3, line4, line5, line6, line7, line8, line9, line10, line11, line12, line13, line14, line15) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", "sssssssssssssssss", [$uuid, $username, $this->getCustomScoreboardLine(0), $this->getCustomScoreboardLine(1), $this->getCustomScoreboardLine(2), $this->getCustomScoreboardLine(3), $this->getCustomScoreboardLine(4), $this->getCustomScoreboardLine(5), $this->getCustomScoreboardLine(6), $this->getCustomScoreboardLine(7), $this->getCustomScoreboardLine(8), $this->getCustomScoreboardLine(9), $this->getCustomScoreboardLine(10), $this->getCustomScoreboardLine(11), $this->getCustomScoreboardLine(12), $this->getCustomScoreboardLine(13), $this->getCustomScoreboardLine(14)]);
    }

    /**
     * @param Item $item
     */
    public function addToInbox(Item $item): void
    {
        if ($this->inbox->getInventory()->firstEmpty() === -1) {
            $this->owner->sendTitle(TextFormat::BOLD . TextFormat::RED . "Full Inventory", TextFormat::GRAY . "Clear out your inbox inventory to receive more!");
            return;
        }
        $this->inbox->getInventory()->setItem($this->inbox->getInventory()->firstEmpty(), $item);
        $this->owner->sendMessage(TextFormat::BOLD . TextFormat::GOLD . "You have an item in your inbox! Use the command /inbox to check them.");
    }

    public function sendInboxInventory(): void
    {
        $this->inbox->send($this->owner);
    }

    /**
     * @return InvMenu
     */
    public function getInbox(): InvMenu
    {
        return $this->inbox;
    }

    /**
     * @return Position[]
     */
    public function getHomes(): array
    {
        return $this->homes;
    }

    /**
     * @param string $name
     *
     * @return null|Position
     */
    public function getHome(string $name): ?Position
    {
        return isset($this->homes[$name]) ? Position::fromObject($this->homes[$name]->add(0.5, 0, 0.5), $this->homes[$name]->getWorld()) : null;
    }

    /**
     * @param string $name
     * @param Position $position
     */
    public function addHome(string $name, Position $position): void
    {
        $uuid = $this->owner->getUniqueId()->toString();
        $username = $this->owner->getName();
        $x = $position->getFloorX();
        $y = $position->getFloorY();
        $z = $position->getFloorZ();
        $level = $position->getWorld()->getDisplayName();
        $this->homes[$name] = $position;
        $stmt = $this->core->getMySQLProvider()->getDatabase()->prepare("INSERT INTO homes(uuid, username, name, x, y, z, level) VALUES(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiis", $uuid, $username, $name, $x, $y, $z, $level);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @param string $name
     */
    public function deleteHome(string $name): void
    {
        $uuid = $this->owner->getUniqueId()->toString();
        unset($this->homes[$name]);
        $stmt = $this->core->getMySQLProvider()->getDatabase()->prepare("DELETE FROM homes WHERE uuid = ? AND name = ?");
        $stmt->bind_param("ss", $uuid, $name);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @return Rank
     */
    public function getRank(): Rank
    {
        return $this->rank;
    }

    public function getCustomScoreboardLine(int $line): string
    {
        if (isset($this->scoreboardCustomLines[$line])) {
            return $this->scoreboardCustomLines[$line];
        } else {
            switch ($line) {
                case 0:
                    return "{empty}";
                case 1:
                    return "§r {player_rank} §r§f{player_username}";
                case 2:
                    return "{empty}";
                case 3:
                    return "§r §l§3STATS";
                case 4:
                    return "§r  §bBalance: §f\${player_balance}";
                case 5:
                    return "§r  §bMob Coins: §f{player_mobcoins}";
                case 6:
                    return "§r  §bPower: §f{player_power}";
                case 7:
                    return "§r  §bLevel: §f{player_level}";
                case 8:
                    return "{empty}";
                case 9:
                    return "§r §d>> store.nexuspe.net";
                case 10:
                    return "§r §d>> vote.nexuspe.net";
                case 11:
                    return "";
                case 12:
                    return "";
                case 13:
                    return "";
                case 14:
                    return "";
            }
        }
    }

    /**
     * @param Rank $rank
     */
    public function setRank(Rank $rank): void
    {
        $this->rank = $rank;
        $this->owner->setPlayerTag();
    }

    /**
     * @param string $permission
     */
    public function addPermission(string $permission): void
    {
        if (in_array($permission, $this->permissions)) {
            return;
        }
        $this->permissions[] = $permission;
    }

    /**
     * @return string[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param string $permission
     */
    public function addPermanentPermission(string $permission): void
    {
        if (in_array($permission, $this->permanentPermissions)) {
            return;
        }
        $this->permanentPermissions[] = $permission;
    }

    /**
     * @return string[]
     */
    public function getPermanentPermissions(): array
    {
        return $this->permanentPermissions;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function getCurrentTag(): string
    {
        return $this->currentTag !== null ? TextFormat::BOLD . TextFormat::DARK_GRAY . " [" . TextFormat::RESET . $this->currentTag . TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_GRAY .  "]" . TextFormat::RESET : "";
    }

    /**
     * @param string $tag
     */
    public function setCurrentTag(string $tag): void
    {
        $this->currentTag = $tag;
    }

    /**
     * @param string $tag
     *
     * @return bool
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags);
    }

    /**
     * @param string $tag
     */
    public function addTag(string $tag): void
    {
        if (in_array($tag, $this->tags)) {
            return;
        }
        $this->tags[] = $tag;
    }

    /**
     * @return Faction|null
     */
    public function getFaction(): ?Faction
    {
        return $this->faction;
    }

    /**
     * @param Faction|null $faction
     */
    public function setFaction(?Faction $faction): void
    {
        $this->faction = $faction;
        $this->owner->setPlayerTag();
        $this->saveDataAsync();
    }

    /**
     * @return int|null
     */
    public function getFactionRole(): ?int
    {
        return $this->factionRole;
    }

    /**
     * @return string
     */
    public function getFactionRoleToString(): string
    {
        switch ($this->factionRole) {
            case Faction::RECRUIT:
                return "";
                break;
            case Faction::MEMBER:
                return "*";
                break;
            case Faction::OFFICER:
                return "**";
                break;
            case Faction::LEADER:
                return "***";
                break;
            default:
                return "";
        }
    }

    /**
     * @param int|null $role
     */
    public function setFactionRole(?int $role): void
    {
        $this->factionRole = $role;
        $this->saveDataAsync();
    }

    /**
     * @return int
     */
    public function getVotePoints(): int
    {
        return $this->votePoints;
    }

    /**
     * @param int $amount
     */
    public function addVotePoints(int $amount = 1)
    {
        $this->votePoints += $amount;
    }

    /**
     * @param int $amount
     */
    public function subtractVotePoints(int $amount)
    {
        $this->votePoints -= $amount;
    }


    /**
     * @return int
     */
    public function getGems(): int
    {
        return $this->gems;
    }

    /**
     * @param int $amount
     */
    public function addGems(int $amount = 1)
    {
        $this->gems += $amount;
    }

    /**
     * @param int $amount
     */
    public function subtractGems(int $amount)
    {
        $this->gems -= $amount;
    }

    /**
     * @param int $amount
     */
    public function setGems(int $amount): void
    {
        $this->gems = $amount;
    }

    /**
     * @return int
     */
    public function getQuestPoints(): int
    {
        return $this->questPoints;
    }

    /**
     * @param int $amount
     */
    public function addQuestPoints(int $amount = 1)
    {
        $this->questPoints += $amount;
    }

    /**
     * @param int $amount
     */
    public function subtractQuestPoints(int $amount)
    {
        $this->questPoints -= $amount;
    }

    /**
     * @return int
     */
    public function getBalance(): int
    {
        return (int)$this->balance;
    }

    /**
     * @param int $amount
     */
    public function addToBalance(int $amount): void
    {
        $this->balance += $amount;
        if (!$this->owner->isAutoSelling()) { // This prevents the message to spam while auto selling. (But i might rewrite this again)
            $this->owner->sendMessage(TextFormat::GREEN . "+ $" . $amount);
        }
    }

    /**
     * @param int $amount
     */
    public function subtractFromBalance(int $amount): void
    {
        $this->balance -= $amount;
        $this->owner->sendMessage(TextFormat::RED . "- $" . $amount);
    }

    /**
     * @param int $amount
     */
    public function setBalance(int $amount): void
    {
        $this->balance = $amount;
        $this->owner->sendMessage(TextFormat::GOLD . "= $" . $amount);
    }

    /**
     * @param int $amount
     */
    public function addKills(int $amount = 1): void
    {
        $this->kills += $amount;
    }

    /**
     * @return int
     */
    public function getKills(): int
    {
        return $this->kills;
    }

    /**
     * @param int $amount
     */
    public function addDeaths(int $amount = 1): void
    {
        $this->deaths += $amount;
    }

    /**
     * @return int
     */
    public function getDeaths(): int
    {
        return $this->deaths;
    }

    /**
     * @return int
     */
    public function getPower(): int
    {
        return (int)$this->power;
    }

    /**
     * @param int $amount
     */
    public function addToPower(int $amount): void
    {
        $this->power += $amount;
        if ($this->getFaction() != null) {
            $this->getFaction()->addStrength($amount);
        }
        $this->owner->sendMessage(TextFormat::GREEN . "+ " . $amount . " Power");
    }

    /**
     * @param int $amount
     */
    public function subtractFromPower(int $amount): void
    {
        $this->power -= $amount;
        if ($this->getFaction() != null) {
            $this->getFaction()->subtractStrength($amount);
        }
        $this->owner->sendMessage(TextFormat::RED . "- " . $amount . " Power");
    }

    /**
     * @param int $amount
     */
    public function setPower(int $amount): void
    {
        $this->power = $amount;
        $this->owner->sendMessage(TextFormat::GOLD . "= $" . $amount . " Power");
    }

    /**
     * @param int $amount
     */
    public function addLuckyBlocksMined(int $amount = 1): void
    {
        $this->luckyBlocks += $amount;
    }

    /**
     * @return int
     */
    public function getLuckyBlocksMined(): int
    {
        return $this->luckyBlocks;
    }

    /**
     * @param Kit $kit
     *
     * @return int
     */
    public function getKitCooldown(Kit $kit): int
    {
        if (!isset($this->kitCooldowns[$kit->getName()])) {
            $this->kitCooldowns[$kit->getName()] = 0;
            return 0;
        }
        return $this->kitCooldowns[$kit->getName()];
    }

    /**
     * @param Kit $kit
     */
    public function setKitCooldown(Kit $kit): void
    {
        $this->kitCooldowns[$kit->getName()] = time();
    }

    /**
     * @param SacredKit $kit
     *
     * @return int
     */
    public function getSacredKitTier(SacredKit $kit): int
    {
        if (!isset($this->sacredKitTiers[$kit->getName()])) {
            $this->sacredKitTiers[$kit->getName()] = 1;
            return 1;
        }
        return $this->sacredKitTiers[$kit->getName()];
    }

    /**
     * @param SacredKit $kit
     */
    public function levelUpSacredKitTier(SacredKit $kit): void
    {
        ++$this->sacredKitTiers[$kit->getName()];
    }

    /**
     * @return int
     */
    public function getOnlineTime(): int
    {
        return $this->onlineTime;
    }

    /**
     * @param int $onlineTime
     */
    public function setOnlineTime(int $onlineTime): void
    {
        $this->onlineTime = $onlineTime;
    }

    /**
     * @param Crate $crate
     *
     * @return int
     */
    public function getKeys(Crate $crate): int
    {
        if (!isset($this->crates[$crate->getName()])) {
            $this->crates[$crate->getName()] = 0;
        }
        return $this->crates[$crate->getName()];
    }

    /**
     * @param Crate $crate
     * @param int $amount
     */
    public function addKeys(Crate $crate, int $amount): void
    {
        $identifier = $crate->getName();
        if (!isset($this->crates[$identifier])) {
            $this->crates[$identifier] = 0;
        }
        $this->crates[$identifier] += max(0, $amount);
        $crate->updateTo($this->owner);
    }

    /**
     * @param Crate $crate
     * @param int $amount
     */
    public function removeKeys(Crate $crate, int $amount = 1): void
    {
        $identifier = $crate->getName();
        if (!isset($this->crates[$identifier])) {
            $this->crates[$identifier] = 0;
        }
        $this->crates[$identifier] -= max(0, $amount);
        $crate->updateTo($this->owner);
    }

    /**
     * @return int
     */
    public function getSellWandUses(): int
    {
        return (int)$this->sellWandUses;
    }

    /**
     * @param int $amount
     */
    public function addToSellWandUses(int $amount): void
    {
        $this->sellWandUses += $amount;
    }

    /**
     * @param int $amount
     */
    public function subtractFromSellWandUses(int $amount): void
    {
        $this->sellWandUses -= $amount;
    }

    /**
     * @param int $amount
     */
    public function setSellWandUses(int $amount): void
    {
        $this->sellWandUses = $amount;
    }

    /**
     * @return Vault[]
     */
    public function getVaults(): array
    {
        return Nexus::getInstance()->getPlayerManager()->getVaultHandler()->getVaultsFor($this->owner->getName());
    }

    /**
     * @param int $id
     *
     * @return Vault|null
     */
    public function getVaultById(int $id): ?Vault
    {
        $vault = Nexus::getInstance()->getPlayerManager()->getVaultHandler()->getVault($this->owner->getName(), $id);
        if ($id <= $this->rank->getVaultsLimit() and $vault === null) {
            $vault = new Vault($this->owner->getName(), $id);
            Nexus::getInstance()->getPlayerManager()->getVaultHandler()->addVault($vault);
            return $vault;
        }
        return $vault;
    }

    /**
     * @param string $id
     *
     * @return Vault|null
     */
    public function getVaultByAlias(string $id): ?Vault
    {
        return Nexus::getInstance()->getPlayerManager()->getVaultHandler()->getVaultByAlias($this->owner->getName(), $id);
    }
}
