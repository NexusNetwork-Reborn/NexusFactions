<?php

namespace Xekvern\Core\Server\Watchdog\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use Xekvern\Core\Server\Watchdog\Forms\PunishMenuForm;
use pocketmine\command\CommandSender;

class PunishCommand extends Command {

    /** @var PunishMenuForm */
    private $form;

    /**
     * PunishCommand constructor.
     */
    public function __construct() {
        parent::__construct("punish", "Open punish menu", "/punish");
        $this->form = new PunishMenuForm();
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof NexusPlayer and $sender->hasPermission("permission.staff")) {
            $sender->sendForm($this->form);
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}