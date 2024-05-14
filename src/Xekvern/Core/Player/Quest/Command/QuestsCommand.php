<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Quest\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Quest\Forms\QuestMainForm;
use Xekvern\Core\Translation\Translation;

class QuestsCommand extends Command {

    /** @var QuestMainForm */
    private $form;

    /**
     * QuestsCommand constructor.
     */
    public function __construct() {
        parent::__construct("quests", "Open quest menu");
        $this->form = new QuestMainForm();
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @throws TranslationException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $sender->sendForm($this->form);
    }
}