<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Forms;

use libs\form\CustomForm;
use libs\form\element\Label;
use pocketmine\utils\TextFormat;
use pocketmine\block\tile\Tile;
use Xekvern\Core\Server\World\WorldEvents;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\World\Tile\Generator;
use pocketmine\block\tile\Spawnable;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\Server;
use Xekvern\Core\Nexus;
use Xekvern\Core\Translation\Translation;
use libs\form\CustomFormResponse;
use pocketmine\player\Player;

class UpgradeEnchantGeneratorUpgrade extends MenuForm {
    /** @var Tile */
    private $tile;


    /**
     * UpgradeSpecialGenerator constructor.
     * 
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player, Tile $tile){
        $title = TextFormat::BOLD . TextFormat::DARK_RED . "Enchantment Generator";
        $this->tile = $tile;
        $text = implode([
            TextFormat::RESET . TextFormat::DARK_BLUE . "Your Balance: XP(" . number_format((int)$player->getXpManager()->getCurrentTotalXp()) . ")",
            "\n" . TextFormat::RESET . TextFormat::DARK_PURPLE . "Rarity Level: " . $tile->getEnchantGeneratorRarityLevel(),
            "\n" . TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Xp Storage Level: " . $tile->getEnchantGeneratorXpStorageLevel(),
            "\n" . TextFormat::RESET . TextFormat::BLUE . "Cost Per Enchant: " . $tile->getEnchantGeneratorCostPerProduction($tile->getEnchantGeneratorProductionCostLevel()),
            "\n" . TextFormat::RESET . TextFormat::DARK_AQUA . "Production Cost Level: " . $tile->getEnchantGeneratorProductionCostLevel(),
            "\n" . TextFormat::RESET . TextFormat::AQUA . "Current Xp: " . $tile->getEnchantGeneratorXpStorage() . "/" . $tile->getEnchantGeneratorMaxXpStorage($tile->getEnchantGeneratorXpStorageLevel()),
        ]);
        $options = [];
        $options[] = new MenuOption("Upgrade Enchant Rarity (" . TextFormat::DARK_GREEN . "XP(" .  ($tile->getEnchantGeneratorRarityLevel() < 5 ? number_format((int)$tile->getEnchantGeneratorCostPerLevel($tile->getEnchantGeneratorRarityLevel())) : "Maxed") . ")" . TextFormat::RESET . TextFormat::DARK_GRAY . ")");
        $options[] = new MenuOption("Upgrade Enchant Xp Storage (" . TextFormat::DARK_GREEN . "XP(" .  ($tile->getEnchantGeneratorXpStorageLevel() < 10 ? number_format((int)$tile->getEnchantGeneratorCostPerLevel($tile->getEnchantGeneratorXpStorageLevel())) : "Maxed") . ")" . TextFormat::RESET . TextFormat::DARK_GRAY . ")");
        $options[] = new MenuOption("Upgrade Enchant Production Cost (" . TextFormat::DARK_GREEN . "XP(" .  ($tile->getEnchantGeneratorProductionCostLevel() < 10 ? number_format((int)$tile->getEnchantGeneratorCostPerLevel($tile->getEnchantGeneratorProductionCostLevel())) : "Maxed") . ")" . TextFormat::RESET . TextFormat::DARK_GRAY . ")");
        parent::__construct($title,$text,$options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     *
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if ($selectedOption === 0) {
            $balance = $player->getXpManager()->getCurrentTotalXp();
            if ($this->tile->getEnchantGeneratorRarityLevel() < 5) {
                if ($balance > $this->tile->getEnchantGeneratorCostPerLevel($this->tile->getEnchantGeneratorRarityLevel())) {
                    if(!$player->getXpManager()->getCurrentTotalXp() > $this->tile->getEnchantGeneratorCostPerLevel($this->tile->getEnchantGeneratorRarityLevel())){
                        return;
                    }
                    $player->getXpManager()->subtractXp($this->tile->getEnchantGeneratorCostPerLevel($this->tile->getEnchantGeneratorRarityLevel()));
                    $this->tile->setEnchantGeneratorRarityLevel($this->tile->getEnchantGeneratorRarityLevel() + 1);
                } else {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                }
            }
        }
        if ($selectedOption === 1) {
            $balance = $player->getXpManager()->getCurrentTotalXp();
            if ($this->tile->getEnchantGeneratorXpStorageLevel() < 10) {
                if ($balance > $this->tile->getEnchantGeneratorCostPerLevel($this->tile->getEnchantGeneratorXpStorageLevel())) {
                    if(!$player->getXpManager()->getCurrentTotalXp() > $this->tile->getEnchantGeneratorCostPerLevel($this->tile->getEnchantGeneratorRarityLevel())){
                        return;
                    }
                    $player->getXpManager()->subtractXp($this->tile->getEnchantGeneratorCostPerLevel($this->tile->getEnchantGeneratorXpStorageLevel()));
                    $this->tile->setEnchantGeneratorXpStorageLevel($this->tile->getEnchantGeneratorXpStorageLevel() + 1);
                } else {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                }
            }
        }
        if ($selectedOption === 2) {
            $balance = $player->getXpManager()->getCurrentTotalXp();
            if ($this->tile->getEnchantGeneratorProductionCostLevel() < 10) {
                if ($balance > $this->tile->getEnchantGeneratorCostPerLevel($this->tile->getEnchantGeneratorProductionCostLevel())) {
                    if(!$player->getXpManager()->getCurrentTotalXp() > $this->tile->getEnchantGeneratorCostPerLevel($this->tile->getEnchantGeneratorRarityLevel())){
                        return;
                    }
                    $player->getXpManager()->subtractXp($this->tile->getEnchantGeneratorCostPerLevel($this->tile->getEnchantGeneratorProductionCostLevel()));
                    $this->tile->setEnchantGeneratorProductionCostLevel($this->tile->getEnchantGeneratorProductionCostLevel() + 1);
                } else {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                }
            }
        }
    }

}
