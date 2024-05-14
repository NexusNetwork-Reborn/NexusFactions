<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Vault\Forms;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\TranslatonException;
use Xekvern\Core\Player\Vault\Vault;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class VaultOptionsForm extends MenuForm {

    /** @var Vault */
    private $vault;

    /**
     * VaultOptionsForm constructor.
     *
     * @param Vault $vault
     */
    public function __construct(Vault $vault) {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Vaults";
        $text = "What would you like to do?";
        $options = [];
        $options[] = new MenuOption("Change alias");
        $options[] = new MenuOption("Open vault");
        $this->vault = $vault;
        parent::__construct($title, $text, $options);
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
        $option = $this->getOption($selectedOption);
        switch($option->getText()) {
            case "Change alias":
                $player->sendForm(new VaultSetAliasForm($this->vault));
                break;
            case "Open vault":
                $player->sendDelayedWindow($this->vault->getMenu());
                break;
        }
    }
}