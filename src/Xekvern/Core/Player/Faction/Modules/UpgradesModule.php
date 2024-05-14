<?php
declare(strict_types=1);

namespace Xekvern\Core\Player\Faction\Modules;

use Xekvern\Core\Player\Faction\Faction;

class UpgradesModule {

    const UPGRADE_KOTH_CAPTURE = "KOTH Capture";
    const UPGRADE_INCREASE_XP = "Increase XP";
    const UPGRADE_BOSS_DAMAGE = "Boss Damage";
    const UPGRADE_SACRED_STONE_CHANCES = "Sacred Stone Chances";
    const UPGRADE_HOLY_BOX_CHANCES = "Holy Box Chances";
    const UPGRADE_MORE_GENERATION_LOOT = "More Generation Loot";

    const UPGRADES = [
        self::UPGRADE_KOTH_CAPTURE,
        self::UPGRADE_INCREASE_XP,
        self::UPGRADE_BOSS_DAMAGE,
        self::UPGRADE_SACRED_STONE_CHANCES,
        self::UPGRADE_HOLY_BOX_CHANCES,
        self::UPGRADE_MORE_GENERATION_LOOT,
    ];

    /** @var Faction */
    private $faction;

    /** @var bool[] */
    private $upgrades;

    /**
     * UpgradesModule constructor.
     *
     * @param Faction $faction
     * @param array $upgrades
     */
    public function __construct(Faction $faction, array $upgrades) {
        $this->faction = $faction;
        $this->upgrades = $upgrades;
    }

    /**
     * @param string $upgrade
     * @param bool $value
     */
    public function setValue(string $upgrade, bool $value): void {
        $this->upgrades[$upgrade] = $value;
    }

    /**
     * @param string $upgrade
     *
     * @return bool
     */
    public function hasUpgrade(string $upgrade): bool {
        if(!isset($this->upgrades[$upgrade])) {
            $this->upgrades[$upgrade] = false;
        }
        return $this->upgrades[$upgrade];
    }

    /**
     * @return bool[]
     */
    public function getUpgrades(): array {
        return $this->upgrades;
    }
}