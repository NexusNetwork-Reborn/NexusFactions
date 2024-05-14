<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Rank;

use Xekvern\Core\Nexus;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class RankHandler {

    /** @var Nexus */
    private $core;

    /** @var Rank[] */
    private $ranks = [];

    /**
     * RankHandler constructor.
     *
     * @param Nexus $core
     *
     * @throws RankException
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $core->getServer()->getPluginManager()->registerEvents(new RankEvents($core), $core);
        $this->init();
    }

    /**
     * @throws RankException
     */
    public function init(): void {
        $this->addRank(new Rank("Player", TextFormat::GRAY, TextFormat::GRAY . TextFormat::BOLD . "PLAYER", Rank::PLAYER,
            TextFormat::GRAY . "⚔" . "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::BOLD . "PLAYER" .  TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::GRAY . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::BOLD . "Player" . TextFormat::WHITE . "{player}", 5, 2, 3, [
                "permission.starter",
                "permission.once"
            ]));
        $this->addRank(new Rank("Subordinate", TextFormat::BLUE, TextFormat::RED . TextFormat::BOLD . "SUBNT", Rank::SUBORDINATE,
            TextFormat::DARK_RED . "⚔" ."{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::RED . TextFormat::BOLD . "SUBORDINATE" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::RED . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::RED . TextFormat::BOLD . "Subordinate" . TextFormat::WHITE . "{player}", 7, 3, 5, [
                "permission.starter",
                "permission.once",
                "permission.subordinate",
            ]));
        $this->addRank(new Rank("Knight", TextFormat::BLUE, TextFormat::BLUE . TextFormat::BOLD . "KNIGHT", Rank::KNIGHT,
            TextFormat::DARK_RED . "⚔" ."{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::BLUE . TextFormat::BOLD . "KNIGHT" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::BLUE . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::BLUE . TextFormat::BOLD . "Knight" . TextFormat::WHITE . "{player}", 8, 5, 7, [
                "permission.starter",
                "permission.once",
                "permission.subordinate",
                "permission.knight",
            ]));
        $this->addRank(new Rank("Hoplite", TextFormat::RED, TextFormat::DARK_RED . TextFormat::BOLD . "HOPLITE", Rank::HOPLITE,
            TextFormat::DARK_RED . "⚔" ."{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::DARK_RED . TextFormat::BOLD . "HOPLITE" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::RED . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::DARK_RED . TextFormat::BOLD . "Hoplite" . TextFormat::WHITE . "{player}", 9, 8, 7, [
                "permission.starter",
                "permission.once",
                "permission.subordinate",
                "permission.knight",
                "permission.hoplite",
                "permission.feed",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.freefix",
            ]));
        $this->addRank(new Rank("Prince", TextFormat::LIGHT_PURPLE, TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "PRINCE", Rank::PRINCE,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "PRINCE" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::LIGHT_PURPLE . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "Prince" . TextFormat::WHITE . "{player}", 12, 10, 10, [
                "permission.starter",
                "permission.once",
                "permission.subordinate",
                "permission.knight",
                "permission.hoplite",
                "permission.prince",
                "permission.feed",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.freefix",
                "permission.autosell",
            ]));
        $this->addRank(new Rank("Titan", TextFormat::GREEN, TextFormat::DARK_GREEN . TextFormat::BOLD . "TITAN", Rank::TITAN,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::DARK_GREEN . TextFormat::BOLD . "PRINCE" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::GREEN . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::DARK_GREEN . TextFormat::BOLD . "Prince" . TextFormat::WHITE . "{player}", 12, 10, 10, [
                "permission.starter",
                "permission.once",
                "permission.subordinate",
                "permission.knight",
                "permission.hoplite",
                "permission.prince",
                "permission.titan",
                "permission.feed",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.freefix",
                "permission.autosell",
            ]));
        $this->addRank(new Rank("Spartan", TextFormat::YELLOW, TextFormat::YELLOW . TextFormat::BOLD . "SPARTAN", Rank::SPARTAN,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::YELLOW . TextFormat::BOLD . "SPARTAN" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::YELLOW . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::YELLOW . TextFormat::BOLD . "Spartan" . TextFormat::WHITE . "{player}", 14, 15, 10, [
                "permission.starter",
                "permission.once",
                "permission.spartan",
                "permission.feed",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.freefix",
                "permission.fly",
                "permission.autosell",
            ]));
        $this->addRank(new Rank("King", TextFormat::YELLOW, TextFormat::GOLD . TextFormat::BOLD . "KING", Rank::KING,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::GOLD . TextFormat::BOLD . "KING" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::YELLOW . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::GOLD . TextFormat::BOLD . "King" . TextFormat::WHITE . "{player}", 18, 25, 15, [
                "permission.starter",
                "permission.once",
                "permission.spartan",
                "permission.king",
                "permission.feed",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.freefix",
                "permission.fixall",
                "permission.fly",
                "permission.join.full",
                "permission.autosell",
            ]));
        $this->addRank(new Rank("Deity", TextFormat::GOLD,TextFormat::WHITE . TextFormat::BOLD . "DEITY", Rank::DEITY,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::WHITE . TextFormat::BOLD . "DEITY" . TextFormat::YELLOW . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::GOLD . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::WHITE . TextFormat::BOLD . "Deity" . TextFormat::WHITE . "{player}", 20, 35, 15, [
                "permission.starter",
                "permission.once",
                "permission.spartan",
                "permission.king",
                "permission.deity",
                "permission.feed",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.freefix",
                "permission.fixall",
                "permission.fly",
                "permission.join.full",
                "permission.autosell",
            ]));
        $this->addRank(new Rank("Trial-Mod", TextFormat::BLUE,TextFormat::BLUE . TextFormat::BOLD . "TRL MOD", Rank::TRIAL_MODERATOR,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::BLUE . TextFormat::BOLD . "TRIAL MODERATOR" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::BLUE . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::BLUE . TextFormat::BOLD . "Trial Moderator" . TextFormat::WHITE . "{player}", 20, 15, 10, [
                "permission.starter",
                "permission.once",
                "permission.feed",
                "permission.fly",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.join.full",
                "permission.staff",
            ]));
        $this->addRank(new Rank("Mod", TextFormat::RED,TextFormat::RED . TextFormat::BOLD . "MOD", Rank::MODERATOR,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::RED . TextFormat::BOLD . "MODERATOR" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::RED . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::RED . TextFormat::BOLD . "Moderator" . TextFormat::WHITE . "{player}", 20, 15, 10, [
                "permission.starter",
                "permission.once",
                "permission.feed",
                "permission.fly",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.join.full",
                "permission.mod",
                "permission.staff",
            ]));
        $this->addRank(new Rank("Senior-Mod", TextFormat::RED,TextFormat::RED . TextFormat::BOLD . "SNR MOD", Rank::SENIOR_MODERATOR,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::RED . TextFormat::BOLD . "SENIOR MODERATOR" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::RED . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::RED . TextFormat::BOLD . "Senior Moderator" . TextFormat::WHITE . "{player}", 20, 20, 10, [
                "permission.starter",
                "permission.once",
                "permission.feed",
                "permission.fly",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.join.full",
                "permission.mod",
                "permission.staff",
            ]));
        $this->addRank(new Rank("Admin", TextFormat::GREEN, TextFormat::GREEN . TextFormat::BOLD . "ADMIN", Rank::ADMIN,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::GREEN . TextFormat::BOLD . "ADMIN" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::GREEN . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::GREEN . TextFormat::BOLD . "Admin" . TextFormat::WHITE . "{player}", 20, 20, 10, [
                "permission.starter",
                "permission.once",
                "permission.spartan",
                "permission.feed",
                "permission.fixall",
                "permission.fly",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.join.full",
                "permission.mod",
                "permission.admin",
                "permission.staff",
            ]));
        $this->addRank(new Rank("Senior-Admin", TextFormat::LIGHT_PURPLE, TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "SNR ADMIN",Rank::SENIOR_ADMIN,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "SENIOR ADMIN" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::LIGHT_PURPLE . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "Senior Admin" . TextFormat::WHITE . "{player}", 20, 30, 10, [
                "permission.starter",
                "permission.once",
                "permission.spartan",
                "permission.king",
                "permission.deity",
                "permission.feed",
                "permission.fly",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.join.full",
                "permission.mod",
                "permission.admin",
                "permission.staff",
            ]));
        $this->addRank(new Rank("Manager", TextFormat::DARK_PURPLE, TextFormat::DARK_PURPLE . TextFormat::BOLD . "MANAGER", Rank::MANAGER,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::DARK_PURPLE . TextFormat::BOLD . "MANAGER" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::LIGHT_PURPLE . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::DARK_PURPLE . TextFormat::BOLD . "Manager" . TextFormat::WHITE . "{player}", 20, 35, 10, [
                "permission.starter",
                "permission.once",
                "permission.spartan",
                "permission.king",
                "permission.deity",
                "permission.feed",
                "permission.fly",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.join.full",
                "permission.mod",
                "permission.admin",
                "permission.staff",
                "permission.setrank",
                "pocketmine.command.teleport",
                "pocketmine.command.ban-ip",
                "pocketmine.command.pardon-ip",
                "deathinventorylog.command",
            ]));
        $this->addRank(new Rank("Owner", TextFormat::DARK_RED, TextFormat::BOLD . TextFormat::DARK_RED . "OWNER", Rank::OWNER,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::BOLD . TextFormat::DARK_RED . TextFormat::BOLD . "OWNER" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::DARK_RED . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::BOLD . TextFormat::DARK_RED . TextFormat::BOLD . "Owner" . TextFormat::WHITE . "{player}", 50, 50, 50, [
                "permission.starter",
                "permission.once",
                "permission.spartan",
                "permission.king",
                "permission.deity",
                "permission.feed",
                "permission.fly",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.join.full",
                "permission.mod",
                "permission.admin",
                "permission.staff",
                "permission.setrank",
                "pocketmine.command.teleport",
                "pocketmine.command.ban-ip",
                "pocketmine.command.pardon-ip",
                "deathinventorylog.command",
            ]));
        $this->addRank(new Rank("YouTube", TextFormat::WHITE,TextFormat::WHITE . TextFormat::BOLD . "Y" . TextFormat::RED . "T", Rank::YOUTUBER,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::WHITE . TextFormat::BOLD . "YOU" . TextFormat::RED . "TUBER" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::WHITE . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::WHITE . TextFormat::BOLD . "You" . TextFormat::RED . "Tuber" . TextFormat::WHITE . "{player}", 20, 15, 15, [
                "permission.starter",
                "permission.once",
                "permission.spartan",
                "permission.feed",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.freefix",
                "permission.fixall",
                "permission.fly",
                "permission.join.full",
            ]));
        $this->addRank(new Rank("Famous", TextFormat::WHITE,TextFormat::BOLD . TextFormat::RED . "FAM" . TextFormat::DARK_RED . "OUS", Rank::FAMOUS,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::RED . TextFormat::BOLD . "FAM" . TextFormat::DARK_RED . "OUS" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::YELLOW . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::RED . TextFormat::BOLD . "FAM" . TextFormat::DARK_RED . "OUS" . TextFormat::WHITE . "{player}", 20, 25, 50, [
                "permission.starter",
                "permission.once",
                "permission.spartan",
                "permission.king",
                "permission.deity",
                "permission.feed",
                "permission.nightvision",
                "permission.luckyblock",
                "permission.freefix",
                "permission.fixall",
                "permission.fly",
                "permission.join.full",
            ]));
        $this->addRank(new Rank("Investor", TextFormat::AQUA, TextFormat::BOLD . TextFormat::AQUA . "INVESTOR", Rank::INVESTOR,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::AQUA . TextFormat::BOLD . "INVESTOR" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . TextFormat::AQUA . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::AQUA . TextFormat::BOLD . "Investor" . TextFormat::WHITE . "{player}", 20, 35, 50, [
                "permission.starter",
                "permission.once",
                "permission.spartan",
                "permission.king",
                "permission.deity",
                "permission.feed",
                "permission.nightvision",
                "permission.freefix",
                "permission.fixall",
                "permission.fly",
                "permission.join.full",
            ]));
        $this->addRank(new Rank("CoOwner", TextFormat::AQUA, TextFormat::BOLD . TextFormat::AQUA . "COOWNER", Rank::COOWNER,
            "{factionRanking}" . TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::BOLD . TextFormat::DARK_BLUE . TextFormat::BOLD . "CO" . TextFormat::LIGHT_PURPLE . "OWNER" . TextFormat::WHITE . " {player}{tag}" . TextFormat::BOLD . TextFormat::GRAY . " » " . TextFormat::RESET . "{message}",
            TextFormat::DARK_AQUA . "{faction_rank}{faction} " . TextFormat::RESET . TextFormat::RESET . TextFormat::GRAY . TextFormat::BOLD . TextFormat::DARK_BLUE . TextFormat::BOLD . "CO" . TextFormat::LIGHT_PURPLE . "OWNER" . TextFormat::WHITE . "{player}", 50, 50, 50, [
            "permission.starter",
            "permission.once",
            "permission.spartan",
            "permission.king",
            "permission.deity",
            "permission.feed",
            "permission.fly",
            "permission.nightvision",
            "permission.luckyblock",
            "permission.join.full",
            "permission.mod",
            "permission.admin",
            "permission.staff",
            "permission.setrank",
            "pocketmine.command.teleport",
            "pocketmine.command.ban-ip",
            "pocketmine.command.pardon-ip",
            "deathinventorylog.command",
        ]));
    }

    /**
     * @param int $identifier
     *
     * @return Rank|null
     */
    public function getRankByIdentifier(int $identifier): ?Rank {
        return $this->ranks[$identifier] ?? null;
    }

    /**
     * @return Rank[]
     */
    public function getRanks(): array {
        return array_unique($this->ranks);
    }

    /**
     * @param string $name
     *
     * @return Rank
     */
    public function getRankByName(string $name): ?Rank {
        return $this->ranks[$name] ?? null;
    }

    /**
     * @param Rank $rank
     *
     * @throws RankException
     */
    public function addRank(Rank $rank): void {
        if(isset($this->ranks[$rank->getIdentifier()]) or isset($this->ranks[$rank->getName()])) {
            throw new RankException("Attempted to override a rank with the identifier of \"{$rank->getIdentifier()}\" and a name of \"{$rank->getName()}\".");
        }
        $this->ranks[$rank->getIdentifier()] = $rank;
        $this->ranks[$rank->getName()] = $rank;
    }
}