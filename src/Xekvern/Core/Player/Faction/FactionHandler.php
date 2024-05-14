<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction;

use Xekvern\Core\Player\Faction\Task\UpdateFactionsTask;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Utils\Utils;
use Xekvern\Core\Player\Faction\Task\UpdateRankingTask;
use Xekvern\Core\Player\Faction\Task\UpdateValueTask;
use Xekvern\Core\Player\Faction\Utils\Claim;
use Xekvern\Core\Player\Faction\Utils\FactionException;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use Xekvern\Core\Player\Faction\Task\IncreasePowerTask;

class FactionHandler
{

    /** @var Nexus */
    private $core;

    /** @var Faction[] */
    private $factions = [];

    /** @var Claim[] */
    private $claims = [];

    private array $facRanking = [];

    /**
     * FactionHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
        $this->init();
        $core->getScheduler()->scheduleRepeatingTask(new UpdateFactionsTask($this), 12000);
        $core->getScheduler()->scheduleRepeatingTask(new UpdateValueTask($this), 140 * 20);
        $core->getScheduler()->scheduleRepeatingTask(new IncreasePowerTask($this), 2700 * 20);
        $core->getServer()->getPluginManager()->registerEvents(new FactionEvents($core), $core);
    }

    public function init(): void
    {
        $stmt = $this->core->getMySQLProvider()->getDatabase()->prepare("SELECT name, x, y, z, members, allies, balance, strength, vault, upgrades, permissions, payoutEmail FROM factions");
        $stmt->execute();
        $stmt->bind_result($name, $x, $y, $z, $members, $allies, $balance, $strength, $vault, $upgrades, $permissions, $payoutEmail);
        while ($stmt->fetch()) {
            $home = null;
            if ($x !== null and $y !== null and $z !== null) {
                $home = new Position($x, $y, $z, Nexus::getInstance()->getServer()->getWorldManager()->getWorldByName(Faction::CLAIM_WORLD));
            }
            $members = explode(",", $members);
            $allyList = [];
            if ($allies !== null) {
                $allyList = explode(",", $allies);
            }
            $inv = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
            $inv->setName(TextFormat::YELLOW . $name . "'s Vault");
            $inv->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory) use ($name): void {
                $items = Nexus::encodeInventory($inventory);
                $stmt = $this->core->getMySQLProvider()->getDatabase()->prepare("UPDATE factions SET vault = ? WHERE name = ?");
                $stmt->bind_param("ss", $items, $name);
                $stmt->execute();
                $stmt->close();
            });
            if ($vault !== null) {
                $items = Nexus::decodeInventory($vault);
                $i = 0;
                foreach ($items as $item) {
                    $inv->getInventory()->setItem($i, $item);
                    ++$i;
                }
            }
            $faction = new Faction($name, $home, $members, $allyList, $balance, $strength, $inv, Utils::decodeBoolArray($upgrades), Utils::decodeArray($permissions), $payoutEmail);
            $this->factions[$name] = $faction;
        }
        $stmt->close();
        $stmt = $this->core->getMySQLProvider()->getDatabase()->prepare("SELECT faction, chunkX, chunkZ, value FROM claims");
        $stmt->execute();
        $stmt->bind_result($fac, $chunkX, $chunkZ, $value);
        while ($stmt->fetch()) {
            $hash = World::chunkHash($chunkX, $chunkZ);
            if (isset($this->factions[$fac])) {
                $this->claims[$hash] = new Claim($chunkX, $chunkZ, $this->factions[$fac], $value);
            }
        }
        $stmt->close();
    }

    /**
     * @return Faction[]
     */
    public function getFactions(): array
    {
        return $this->factions;
    }

    /**
     * @param string $name
     *
     * @return Faction|null
     */
    public function getFaction(string $name): ?Faction
    {
        if (isset($this->factions[$name])) {
            return $this->factions[$name];
        }
        return null;
    }

    /**
     * @param string $name
     * @param NexusPlayer $leader
     *
     * @throws FactionException
     */
    public function createFaction(string $name, NexusPlayer $leader): void
    {
        if (isset($this->factions[$name])) {
            throw new FactionException("Unable to override an existing faction!");
        }
        $members = $leader->getName();
        $stmt = $this->core->getMySQLProvider()->getDatabase()->prepare("INSERT INTO factions(name, members) VALUES(?, ?)");
        $stmt->bind_param("ss", $name, $members);
        $stmt->execute();
        $stmt->close();
        $inv = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $inv->setName(TextFormat::YELLOW . $name . "'s Vault");
        $inv->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory) use ($name): void {
            $items = Nexus::encodeInventory($inventory);
            $stmt = $this->core->getMySQLProvider()->getDatabase()->prepare("UPDATE factions SET vault = ? WHERE name = ?");
            $stmt->bind_param("ss", $items, $name);
            $stmt->execute();
            $stmt->close();
        });
        $faction = new Faction($name, null, [$members], [], 0, $leader->getDataSession()->getPower(), $inv, [], [], "");
        $faction->scheduleUpdate();
        $this->factions[$name] = $faction;
        $leader->getDataSession()->setFaction($faction);
        $leader->getDataSession()->setFactionRole(Faction::LEADER);
        $leader->getDataSession()->saveDataAsync();
    }

    /**
     * @param string $name
     */
    public function removeFaction(string $name): void
    {
        if (isset($this->factions[$name])) {
            unset($this->factions[$name]);
            $database = Nexus::getInstance()->getMySQLProvider()->getDatabase();
            $stmt = $database->prepare("DELETE FROM factions WHERE name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * @param Claim $claim
     */
    public function addClaim(Claim $claim)
    {
        $chunkX = $claim->getChunkX();
        $chunkZ = $claim->getChunkZ();
        $claim->scheduleUpdate();
        $this->claims[World::chunkHash($chunkX, $chunkZ)] = $claim;
    }

    /**
     * @param Claim $claim
     */
    public function removeClaim(Claim $claim)
    {
        $chunkX = $claim->getChunkX();
        $chunkZ = $claim->getChunkZ();
        $name = $claim->getFaction()->getName();
        unset($this->claims[World::chunkHash($chunkX, $chunkZ)]);
        $database = Nexus::getInstance()->getMySQLProvider()->getDatabase();
        $stmt = $database->prepare("DELETE FROM claims WHERE faction = ? AND chunkX = ? AND chunkZ = ?");
        $stmt->bind_param("sii", $name, $chunkX, $chunkZ);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @param Faction $faction
     */
    public function removeClaimsOf(Faction $faction): void
    {
        foreach ($this->claims as $hash => $claim) {
            if ($claim->getFaction()->getName() === $faction->getName()) {
                unset($this->claims[$hash]);
            }
        }
        $database = Nexus::getInstance()->getMySQLProvider()->getDatabase();
        $name = $faction->getName();
        $stmt = $database->prepare("DELETE FROM claims WHERE faction = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @param Faction $faction
     *
     * @return Claim[]
     */
    public function getClaimsOf(Faction $faction): array
    {
        $claims = [];
        foreach ($this->claims as $hash => $claim) {
            if ($claim->getFaction()->getName() === $faction->getName()) {
                $claims[] = $this->claims[$hash];
            }
        }
        return $claims;
    }

    /**
     * @param Faction $faction
     * @param Claim $claim
     *
     * @throws FactionException
     */
    public function overClaim(Faction $faction, Claim $claim)
    {
        $chunkX = $claim->getChunkX();
        $chunkZ = $claim->getChunkZ();
        if (!isset($this->claims[World::chunkHash($chunkX, $chunkZ)])) {
            throw new FactionException("Invalid claim that's trying to be overclaimed.");
        }
        $this->claims[World::chunkHash($chunkX, $chunkZ)]->setFaction($faction);
    }

    /**
     * @param Position $position
     *
     * @return Claim|null
     */
    public function getClaimInPosition(Position $position): ?Claim
    {
        if ($position->getWorld() === null or $position->getWorld()->getDisplayName() !== Faction::CLAIM_WORLD) {
            return null;
        }
        $x = $position->getX();
        $z = $position->getZ();
        $hash = World::chunkHash((int)$x >> 4, (int)$z >> 4);
        return $this->claims[$hash] ?? null;
    }

    /**
     * @param int $hash
     *
     * @return Claim|null
     */
    public function getClaimByHash(int $hash): ?Claim
    {
        return $this->claims[$hash] ?? null;
    }

    /**
     * @return Claim[]
     */
    public function getClaims(): array
    {
        return $this->claims;
    }

    /**
     * @param NexusPlayer $player
     *
     * @return string[]
     */
    public static function sendFactionMap(NexusPlayer $player): array
    {
        $chunkX = (int)$player->getPosition()->getX() >> 4;
        $chunkZ = (int)$player->getPosition()->getZ() >> 4;
        $lines = [];
        $factions = [];
        $claim = "None";
        if (($currentClaim = Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getClaimInPosition($player->getPosition())) !== null) {
            $claim = $currentClaim->getFaction()->getName();
        }
        $lines[] = TextFormat::LIGHT_PURPLE . " " . Utils::getMapBlock() . " " . TextFormat::DARK_GRAY . "- " . TextFormat::GRAY . "You ({$player->getPosition()->getFloorX()}, {$player->getPosition()->getFloorZ()})";
        foreach (array_unique($factions) as $location => $faction) {
            $lines[] = TextFormat::DARK_RED . " " . Utils::getMapBlock() . " " . TextFormat::DARK_GRAY . "- " . TextFormat::GRAY . "$faction $location";
        }
        $lines[] = TextFormat::GREEN . " Current claim: " . TextFormat::WHITE . $claim;
        $lines[] = " ";
        for ($x = $chunkX - 3; $x <= $chunkX + 3; $x++) {
            $line = "";
            for ($z = $chunkZ - 5; $z <= $chunkZ + 5; $z++) {
                if ($x === $chunkX and $z === $chunkZ) {
                    $line .= TextFormat::LIGHT_PURPLE . Utils::getMapBlock();
                    continue;
                }
                if (($claim = Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getClaimByHash(World::chunkHash($x, $z))) !== null and $player->getWorld()->getDisplayName() === Faction::CLAIM_WORLD) {
                    $line .= TextFormat::DARK_RED . Utils::getMapBlock();
                    $normalX = $x << 4;
                    $normalZ = $z << 4;
                    $factions["($normalX, $normalZ)"] = $claim->getFaction()->getName();
                    continue;
                }
                $line .= TextFormat::GRAY . Utils::getMapBlock();
            }
            $lines[] = "  " . $line;
        }
        if (count($lines) > 11) {
            return array_splice($lines, 0, 11);
        }
        return $lines;
    }

    /**
    * @param string $name
    * @return bool
    */
    public function factionExists(string $name): bool {
        $database = Nexus::getInstance()->getMySQLProvider()->getDatabase();
        $query = "SELECT COUNT(*) as count FROM factions WHERE LOWER(name) = LOWER(?)";
        $stmt = $database->prepare($query);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return intval($row['count']) > 0;
        }
        return false;
    }
    

    /**
     * @param string $name
     * @return int
     */
    public function getFactionRanking(string $name): ?int {
        $factions = $this->getFactions();
        $faction = $this->getFaction($name);
        if ($faction === null) {
            return null;
        }
        $value = [];
        foreach ($factions as $facName => $faction) {
            $value[$facName] = $faction->getStrength();
        }
        arsort($value);
        return array_search($name, array_keys($value)) + 1;
    }

    /**
     * @param string $name
     * @return int
     */
    public function formatRanking(string $name, bool $forceShow = false): string {
        $rank = $this->getFactionRanking($name);
        if ($rank === null) {
            if ($forceShow) {
                return TextFormat::WHITE . "#?";
            }
            return "";
        }

        $color = match ($rank) {
            1 => TextFormat::GREEN,
            2 => TextFormat::YELLOW,
            3 => TextFormat::RED,
            default => TextFormat::WHITE
        };
        return $color . "#" . $rank;
    }

    function calculatePower($balance, $k = 1) {
        $logFactor = log($balance + 1) / log(19224000);
        $power = ($k * log($balance / 20500000)) / log(2);
        $power *= $logFactor;
        if($power < 0.25){
            $power = 0.25;
        }
        if (is_nan($power)) {
            $power = 0.25;
        }
        return $power;
    }
}   