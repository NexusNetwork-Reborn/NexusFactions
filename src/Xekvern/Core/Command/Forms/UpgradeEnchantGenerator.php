<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Forms;

use libs\form\CustomForm;
use libs\form\element\Label;
use pocketmine\block\GlazedTerracotta;
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

class UpgradeEnchantGenerator extends MenuForm {
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
        $options[] = new MenuOption("Upgrade Enchant Generator");
        $options[] = new MenuOption("Refuel Enchant Generator");
        parent::__construct($title,$text,$options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     *
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if($selectedOption === 0){
            $player->sendForm(new UpgradeEnchantGeneratorUpgrade($player, $this->tile)); 
        }
        if($selectedOption === 1){
            $remainingXP = (int)($this->tile->getEnchantGeneratorMaxXpStorage($this->tile->getEnchantGeneratorXpStorageLevel()) - $this->tile->getEnchantGeneratorXpStorage());
            $player->sendMessage(TextFormat::GREEN . "Temporarily Disabled " . $remainingXP . " XP");
            $this->tile->setEnchantGeneratorXpStorage($this->tile->getEnchantGeneratorMaxXpStorage($this->tile->getEnchantGeneratorXpStorageLevel()));
            // $playerXP = $player->getXpManager()->getCurrentTotalXp();
            // if($playerXP > $remainingXP){
            //     $player->getXpManager()->subtractXp($remainingXP);
            //     $this->tile->setEnchantGeneratorXpStorage($remainingXP + $this->tile->getEnchantGeneratorXpStorage());
            //     $player->sendMessage(TextFormat::GREEN . "Added " . $remainingXP . " XP");
            // }else{
            //     $player->getXpManager()->subtractXp($playerXP + $this->tile->getEnchantGeneratorXpStorage());
            //     $this->tile->setEnchantGeneratorXpStorage($playerXP);
            //     $player->sendMessage(TextFormat::GREEN . "Added " . $playerXP . " XP");
            //}
        }
    }

}
