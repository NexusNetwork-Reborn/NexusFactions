<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Vault\Forms;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use Xekvern\Core\Player\Vault\Vault;
use libs\form\CustomForm;
use libs\form\CustomFormResponse;
use libs\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class VaultSetAliasForm extends CustomForm {

    /** @var Vault */
    private $vault;

    /**
     * VaultSetAliasForm constructor.
     *
     * @param Vault $vault
     */
    public function __construct(Vault $vault) {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Vaults";
        $elements[] = new Input("Alias", "Use only letters (24 character limit)");
        $this->vault = $vault;
        parent::__construct($title, $elements);
    }

    /**
     * @param Player $player
     * @param CustomFormResponse $data
     *
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, CustomFormResponse $data): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $alias = $data->getString("Alias");
        if(strlen($alias) > 24) {
            $player->sendMessage(Translation::getMessage("nameTooLong"));
            return;
        }
        if(!preg_match("/^[a-zA-Z]+$/", $alias)) {
            $player->sendMessage(Translation::getMessage("onlyLetters"));
            return;
        }
        $this->vault->setAlias($alias);
        Nexus::getInstance()->getPlayerManager()->getVaultHandler()->addVault($this->vault);
        $player->sendMessage(TextFormat::GREEN . "PV #" . $this->vault->getId() . "'s alias has been set to \"$alias\".");
    }
}