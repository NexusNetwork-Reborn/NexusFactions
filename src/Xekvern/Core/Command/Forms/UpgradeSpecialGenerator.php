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

class UpgradeSpecialGenerator extends MenuForm
{

    /** @var Tile */
    private $tile;

    /**
     * UpgradeSpecialGenerator constructor.
     * 
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player, Tile $tile)
    {
        $title = TextFormat::BOLD . TextFormat::DARK_PURPLE . "Amethyst Generator";
        if (!$tile instanceof Generator) {
            return;
        }
        $this->tile = $tile;
        $text = implode([
            TextFormat::RESET . TextFormat::BLUE . "Your Balance: $" . number_format((int)$player->getDataSession()->getBalance()),
            "\n" . TextFormat::RESET . TextFormat::DARK_GREEN . "Money Generation: " . ($tile->getSpecialMoneyLocked() === 0 ? TextFormat::RED . "Locked" : TextFormat::GREEN . "Unlocked"),
            "\n" . TextFormat::RESET . TextFormat::DARK_GREEN . "Money Level: " . $tile->getSpecialMoneyLevel(),
            "\n" . TextFormat::RESET . TextFormat::LIGHT_PURPLE . "XP Generation: " . ($tile->getSpecialXpLocked() === 0 ? TextFormat::RED . "Locked" : TextFormat::GREEN . "Unlocked"),
            "\n" . TextFormat::RESET . TextFormat::LIGHT_PURPLE . "XP Level: " . $tile->getSpecialXpLevel(),
            "\n" . TextFormat::RESET . "\n",
        ]);
        $options = [];
        if ($tile->getSpecialMoneyLocked() === 0) {
            $options[] = new MenuOption("Unlock Money Generation (" . TextFormat::DARK_GREEN . "$" .  number_format((int)$tile->getCostForUnlock()) . TextFormat::RESET . ")");
        } else {
            $options[] = new MenuOption("Upgrade Money Generation (" . TextFormat::DARK_GREEN . "$" .  ($tile->getSpecialMoneyLevel() < 10 ? number_format((int)$tile->getCostForLevel($tile->getSpecialMoneyLevel())) : "Maxed") . TextFormat::RESET . TextFormat::DARK_GRAY . ")");
        }

        if ($tile->getSpecialXpLocked() === 0) {
            $options[] = new MenuOption("Unlock Experience Generation (" . TextFormat::DARK_GREEN . "$" .  number_format((int)$tile->getCostForUnlock()) . TextFormat::RESET . ")");
        } else {
            $options[] = new MenuOption("Upgrade Experience Generation (" . TextFormat::DARK_GREEN . "$" .  ($tile->getSpecialXpLevel() < 10 ? number_format((int)$tile->getCostForLevel($tile->getSpecialXpLevel())) : "Maxed") . TextFormat::RESET . TextFormat::DARK_GRAY . ")");
        }
        parent::__construct($title, $text, $options);
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
        if (!$this->tile instanceof Generator) {
            return;
        }
        if ($selectedOption === 0) {
            $balance = $player->getDataSession()->getBalance();
            if ($this->tile->getSpecialMoneyLocked() === 0) {
                if ($balance > $this->tile->getCostForUnlock()) {
                    $player->getDataSession()->subtractFromBalance($this->tile->getCostForUnlock());
                    $this->tile->setSpecialMoneyLocked(1);
                } else {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                }
            } elseif ($this->tile->getSpecialMoneyLevel() < 10) {
                if ($balance > $this->tile->getCostForLevel($this->tile->getSpecialMoneyLevel())) {
                    $player->getDataSession()->subtractFromBalance($this->tile->getCostForLevel($this->tile->getSpecialMoneyLevel()));
                    $this->tile->setSpecialMoneyLevel($this->tile->getSpecialMoneyLevel() + 1);
                } else {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                }
            }
        }
        if ($selectedOption === 1) {
            $balance = $player->getDataSession()->getBalance();
            if ($this->tile->getSpecialXpLocked() === 0) {
                if ($balance > $this->tile->getCostForUnlock()) {
                    $player->getDataSession()->subtractFromBalance($this->tile->getCostForUnlock());
                    $this->tile->setSpecialXpLocked(1);
                } else {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                }
            } elseif ($this->tile->getSpecialXpLevel() < 10) {
                if ($balance > $this->tile->getCostForLevel($this->tile->getSpecialXpLevel())) {
                    $player->getDataSession()->subtractFromBalance($this->tile->getCostForLevel($this->tile->getSpecialXpLevel()));
                    $this->tile->setSpecialXpLevel($this->tile->getSpecialXpLevel() + 1);
                } else {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                }
            }
        }
    }
}
