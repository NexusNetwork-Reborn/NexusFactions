<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Vault\Forms;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\TranslatonException;
use Xekvern\Core\Player\Vault\Vault;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class VaultListForm extends MenuForm {

    /** @var Vault[] */
    private $vaults;

    /**
     * VaultListForm constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player) {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Vaults";
        $text = "Select a vault.";
        $options = [];
        $this->vaults = Nexus::getInstance()->getPlayerManager()->getVaultHandler()->getVaultsFor($player->getName());
        $vaultLimit = $player->getDataSession()->getRank()->getVaultsLimit();
        for($i = 1; $i <= $vaultLimit; $i++) {
            if(isset($this->vaults[$i])) {
                $vault = $this->vaults[$i];
                $alias = $vault->getAlias() !== null ? $vault->getAlias() : "None";
                $options[] = new MenuOption(TextFormat::YELLOW . TextFormat::BOLD . "PV #$i (Alias: $alias)\n" . TextFormat::RESET . TextFormat::GREEN . "(Used)");
                continue;
            }
            $options[] = new MenuOption(TextFormat::YELLOW . TextFormat::BOLD . "PV #$i" . "\n" . TextFormat::RESET . TextFormat::GRAY . "(Unused)");
        }
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
        $number = $selectedOption + 1;
        if(!isset($this->vaults[$number])) {
            $vault = $player->getDataSession()->getVaultById($number);
        }
        else {
            $vault = $this->vaults[$number];
        }
        $player->sendDelayedForm(new VaultOptionsForm($vault));
    }
}