<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Forms\ChangeLogForm;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class ChangeLogCommand extends Command {

    /** @var ChangeLogForm */
    private $form;

    /**
     * ChangeLogCommand constructor.
     */
    public function __construct() {
        parent::__construct("changelog", "Open change log");
        $this->form = new ChangeLogForm();
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            if($sender instanceof NexusPlayer) { $sender->playErrorSound(); }
            return;
        }
        $sender->sendForm($this->form);
    }
}