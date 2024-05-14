<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Command;

use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\Forms\CEMenuForm;

class CEInfoCommand extends Command
{

    /** @var CEMenuForm */
    private $form;

    /**
     * CEInfoCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("ceinfo", "Check what each custom enchantment does.");
        $this->form = new CEMenuForm();
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $sender->sendForm($this->form);
    }
}
