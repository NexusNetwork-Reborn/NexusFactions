<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Utils\Utils;
use muqsit\invmenu\InvMenu;
use pocketmine\Server;
use pocketmine\world\Position;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;
use Xekvern\Core\Player\Faction\Modules\UpgradesModule;

class Faction {

    const RECRUIT = 0;
    const MEMBER = 1;
    const OFFICER = 2;
    const LEADER = 3;
    const MAX_MEMBERS = 20;
    const MAX_ALLIES = 1;
    const MEMBERS_NEEDED_TO_CLAIM = 1;
    const CLAIMS_PER_MEMBER = 2;
    const CLAIM_WORLD = "wild";
    const POWER_PER_KILL = 5;
    const POWER_PER_JOIN = 10;
    const POWER_PER_ALLY = 15;
    
    /** @var string */
    private $name;

    /** @var string[] */
    private $members;

    /** @var string[] */
    private $invites = [];

    /** @var Faction[] */
    private $allies;

    /** @var string[] */
    private $allyRequests = [];

    /** @var int */
    private $balance;

    /** @var int */
    private $strength;
    
    /** @var int */
    private $claimValue;

    /** @var null|Position */
    private $home;

    /** @var InvMenu */
    private $vault;

    /** @var PermissionsModule */
    private $permissionsModule;

    /** @var UpgradesModule */
    private $upgradesModule;

    /** @var null|string */
    private $payoutEmail;

    /** @var bool */
    private $needsUpdate = false;

    /**
     * Faction constructor.
     *
     * @param string $name
     * @param Position|null $home
     * @param array $members
     * @param array $allies
     * @param int $balance
     * @param int $strength
     * @param InvMenu $vault
     * @param array $upgrades
     * @param array $permissions
     * @param string $payoutEmail
     *
     * @throws FactionException
     */
    public function __construct(string $name, ?Position $home, array $members, array $allies, int $balance, int $strength, InvMenu $vault, array $upgrades, array $permissions, string $payoutEmail) {
        $this->name = $name;
        $this->members = $members;
        $this->allies = $allies;
        $this->balance = $balance;
        $this->strength = $strength;
        $this->home = $home;
        $this->vault = $vault;
        $this->permissionsModule = new PermissionsModule($this, $permissions);
        $this->upgradesModule = new UpgradesModule($this, $upgrades);
        $this->payoutEmail = $payoutEmail;
        $this->claimValue = 0;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return string[]
     */
    public function getMembers(): array {
        $this->members = array_unique($this->members);
        return $this->members;
    }

    /**
     * @return NexusPlayer[]
     */
    public function getOnlineMembers(): array {
        $members = [];
        foreach($this->getMembers() as $member) {
            $player = Server::getInstance()->getPlayerExact($member);
            if($player !== null) {
                $members[] = $player;
            }
        }
        return $members;
    }

    /**
     * @param string $player
     *
     * @return bool
     */
    public function isInFaction(string $player): bool {
        return in_array($player, $this->getMembers());
    }

    /**
     * @param NexusPlayer $member
     */
    public function addMember(NexusPlayer $member): void {
        $this->members[] = $member->getName();
        $this->addStrength($member->getDataSession()->getPower());
        $member->getDataSession()->setFaction($this);
        $member->getDataSession()->setFactionRole(self::RECRUIT);
        $this->scheduleUpdate();
    }

    /**
     * @param string $player
     */
    public function removeMember(string $player): void {
        unset($this->members[array_search($player, $this->members)]);
        $member = Server::getInstance()->getPlayerExact($player);
        if($member instanceof NexusPlayer) {
            $member->getDataSession()->setFaction(null);
            $member->getDataSession()->setFactionRole(null);
            $this->subtractStrength($member->getDataSession()->getPower());
        } else {
            $stmt = Nexus::getInstance()->getMySQLProvider()->getDatabase()->prepare("SELECT power FROM stats WHERE username = ?");
            $stmt->bind_param("s", $player);
            $stmt->execute();
            $stmt->bind_result($power);
            $stmt->fetch();
            $stmt->close();
            if(isset($power)) {
                $this->subtractStrength($power);
            }
        }
        $members = implode(",", $this->members);
        $stmt = Nexus::getInstance()->getMySQLProvider()->getDatabase()->prepare("UPDATE factions SET members = ? WHERE name = ?");
        $stmt->bind_param("ss", $members, $this->name);
        $stmt->execute();
        $stmt->close();
        $this->scheduleUpdate();
    }

    /**
     * @param NexusPlayer $player
     *
     * @return bool
     */
    public function isInvited(NexusPlayer $player): bool {
        return in_array($player->getName(), $this->invites);
    }

    /**
     * @param NexusPlayer $player
     */
    public function addInvite(NexusPlayer $player): void {
        if(!in_array($player->getName(), $this->invites)) {
            $this->invites[] = $player->getName();
        }
    }

    /**
     * @param NexusPlayer $player
     */
    public function removeInvite(NexusPlayer $player): void {
        unset($this->invites[array_search($player->getName(), $this->invites)]);
    }

    /**
     * @param Faction $faction
     *
     * @return bool
     */
    public function isAllying(Faction $faction): bool {
        return in_array($faction->getName(), $this->allyRequests);
    }

    /**
     * @param Faction $faction
     */
    public function addAllyRequest(Faction $faction): void {
        if(!in_array($faction->getName(), $this->allyRequests)) {
            $this->allyRequests[] = $faction->getName();
        }
    }

    /**
     * @param Faction $faction
     */
    public function removeAllyRequest(Faction $faction): void {
        unset($this->allyRequests[array_search($faction->getName(), $this->allyRequests)]);
    }

    /**
     * @param Faction $faction
     */
    public function addAlly(Faction $faction): void {
        $this->allies[] = $faction->getName();
        $this->removeAllyRequest($faction);
        $this->scheduleUpdate();
    }

    /**
     * @param Faction $faction
     */
    public function removeAlly(Faction $faction): void {
        unset($this->allies[array_search($faction->getName(), $this->allies)]);
        $this->scheduleUpdate();
    }

    /**
     * @return string[]
     */
    public function getAllies(): array {
        $allies = [];
        $this->allies = array_unique($this->allies);
        foreach($this->allies as $ally) {
            $ally = Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getFaction($ally);
            if($ally !== null) {
                if($ally->isAlly($this)) {
                    $allies[] = $ally->getName();
                }
            }
        }
        return $allies;
    }

    /**
     * @param Faction $faction
     *
     * @return bool
     */
    public function isAlly(Faction $faction): bool {
        return in_array($faction->getName(), $this->allies);
    }

    public function getClaimLimit(): int {
        return 55;
    }

    /**
     * @param int $amount
     */
    public function addMoney(int $amount): void {
        $this->balance += $amount;
    }

    /**
     * @param int $amount
     */
    public function setBalance(int $amount): void {
        $this->balance = $amount;
    }

    /**
     * @param int $amount
     */
    public function subtractMoney(int $amount): void {
        $this->balance -= $amount;
    }

    /**
     * @return int
     */
    public function getBalance(): int {
        return $this->balance;
    }

    /**
     * @param int $amount
     */
    public function addStrength(int $amount): void {
        $this->strength += $amount;
    }

    /**
     * @param int $amount
     */
    public function setStrength(int $amount): void {
        $this->strength = $amount;
    }

    /**
     * @param int $amount
     */
    public function subtractStrength(int $amount): void {
        $this->strength -= $amount;
    }

    /**
     * @return int
     */
    public function getStrength(): int {
        return $this->strength;
    }

    /**
     * @return int
     */
    public function getValue(): int {
        $claims = Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getClaimsOf($this);
        $value = 0;
        foreach($claims as $claim) {
            $value += $claim->getValue();
        }
        return $value;
    }

    /**
     * @param int $amount
     */
    public function setClaimValue(int $amount): void {
        $this->claimValue = $amount;
    }

    /**
     * @return int
     */
    public function getClaimValue(): int {
        return $this->claimValue;
    }

    /**
     * @param Position|null $position
     */
    public function setHome(?Position $position = null): void {
        $this->home = $position;
    }

    /**
     * @return Position|null
     */
    public function getHome(): ?Position {
        return $this->home;
    }

    /**
     * @param NexusPlayer $player
     */
    public function sendVault(NexusPlayer $player): void {
        $this->vault->send($player);
    }

    /**
     * @return PermissionsModule
     */
    public function getPermissionsModule(): PermissionsModule {
        return $this->permissionsModule;
    }

    /**
     * @return UpgradesModule
     */
    public function getUpgradesModule(): UpgradesModule {
        return $this->upgradesModule;
    }

    /**
     * @return string
     */
    public function getPayoutEmail(): string {
        return $this->payoutEmail;
    }

    /**
     * @param string $payoutEmail
     */
    public function setPayoutEmail(string $payoutEmail): void {
        $this->payoutEmail = $payoutEmail;
        $this->scheduleUpdate();
    }

    public function scheduleUpdate(): void {
        $this->needsUpdate = true;
    }

    /**
     * @return bool
     */
    public function needsUpdate(): bool {
        return $this->needsUpdate;
    }

    public function disband(): void {
        foreach($this->getOnlineMembers() as $member) {
            if($member->isLoaded()) {
                $member->getDataSession()->setFaction(null);
                $member->getDataSession()->setFactionRole(null);
            }
        }
        $database = Nexus::getInstance()->getMySQLProvider()->getDatabase();
        $null = null;
        $stmt = $database->prepare("UPDATE stats SET faction = ?, factionRole = ? WHERE faction = ?");
        $stmt->bind_param("sss", $null, $null, $name);
        $stmt->execute();
        $stmt->close();
        $manager = Nexus::getInstance()->getPlayerManager()->getFactionHandler();
        $manager->removeClaimsOf($this);
        foreach($this->allies as $ally) {
            $ally = $manager->getFaction($ally);
            if($ally !== null and $ally->isAlly($this)) {
                $ally->removeAlly($this);
            }
        }
        $manager->removeFaction($this->name);
    }

    public function updateAsync(): void {
        if($this->needsUpdate) {
            $this->needsUpdate = false;
            $members = implode(",", $this->getMembers());
            $allyList = implode(",", $this->allies);
            $x = null;
            $y = null;
            $z = null;
            if($this->home !== null) {
                $x = $this->home->getX();
                $y = $this->home->getY();
                $z = $this->home->getZ();
            }
            $upgrades = Utils::encodeBoolArray($this->upgradesModule->getUpgrades());
            $permissions = Utils::encodeArray($this->permissionsModule->getPermissions());
            $inv = Nexus::encodeInventory($this->vault->getInventory());
            $connector = Nexus::getInstance()->getMySQLProvider()->getConnector();
            $connector->executeUpdate("REPLACE INTO factions(name, x, y, z, members, allies, balance, strength, upgrades, vault, permissions, payoutEmail) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", "siiissiissss", [
                $this->name,
                $x,
                $y,
                $z,
                $members,
                $allyList,
                $this->balance,
                $this->strength,
                $upgrades,
                $inv,
                $permissions,
                $this->payoutEmail
            ]);
        }
    }

    public function update(): void {
        if($this->needsUpdate) {
            $database = Nexus::getInstance()->getMySQLProvider()->getDatabase();
            $this->needsUpdate = false;
            $members = implode(",", $this->getMembers());
            $allyList = implode(",", $this->allies);
            $x = null;
            $y = null;
            $z = null;
            if($this->home !== null) {
                $x = $this->home->getX();
                $y = $this->home->getY();
                $z = $this->home->getZ();
            }
            $upgrades = Utils::encodeBoolArray($this->upgradesModule->getUpgrades());
            $permissions = Utils::encodeArray($this->permissionsModule->getPermissions());
            $inv = Nexus::encodeInventory($this->vault->getInventory());
            $stmt = $database->prepare("REPLACE INTO factions(name, x, y, z, members, allies, balance, strength, upgrades, vault, permissions, payoutEmail) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiissiissss", $this->name, $x, $y, $z, $members, $allyList, $this->balance, $this->strength, $upgrades, $inv, $permissions, $this->payoutEmail);
            $stmt->execute();
            $stmt->close();
        }
    }
}