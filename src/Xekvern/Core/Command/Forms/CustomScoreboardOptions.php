<?php

declare(strict_types=1);

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

class CustomScoreboardOptions extends MenuForm
{

    /** @var Tile */
    private $tile;

    /**
     * UpgradeSpecialGenerator constructor.
     * 
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player)
    {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Scoreboard Customization";
        $text = implode([
            "",
            "\n Line 1: " . $this->replaceSpecialCharacter(0, $player),
            "\n Line 2: " . $this->replaceSpecialCharacter(1, $player),
            "\n Line 3: " . $this->replaceSpecialCharacter(2, $player),
            "\n Line 4: " . $this->replaceSpecialCharacter(3, $player),
            "\n Line 5: " . $this->replaceSpecialCharacter(4, $player),
            "\n Line 6: " . $this->replaceSpecialCharacter(5, $player),
            "\n Line 7: " . $this->replaceSpecialCharacter(6, $player),
            "\n Line 8: " . $this->replaceSpecialCharacter(7, $player),
            "\n Line 9: " . $this->replaceSpecialCharacter(8, $player),
            "\n Line 10: " . $this->replaceSpecialCharacter(9, $player),
            "\n Line 11: " . $this->replaceSpecialCharacter(10, $player),
            "\n Line 12: " . $this->replaceSpecialCharacter(11, $player),
            "\n Line 13: " . $this->replaceSpecialCharacter(12, $player),
            "\n Line 14: " . $this->replaceSpecialCharacter(13, $player),
            "\n Line 15: " . $this->replaceSpecialCharacter(14, $player)
        ]);
        $options = [];
        $options[] = new MenuOption("Reset Scoreboard");
        $options[] = new MenuOption("Edit Scoreboard");
        parent::__construct($title, $text, $options);
    }

    public function replaceSpecialCharacter(int $line, NexusPlayer $player): String{
        $lineString = $player->getDataSession()->getCustomScoreboardLine($line);
        return str_replace("§", "\§", $lineString);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if ($selectedOption === 0) {
            var_dump("reset");
        }
        if ($selectedOption === 1) {
            var_dump("edit");
        }
    }
}
