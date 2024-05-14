<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Gamble\Command\SubCommands;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use Xekvern\Core\Player\Gamble\Command\Forms\CoinFlipCreateForm;
use Xekvern\Core\Nexus;

class AddSubCommand extends SubCommand {

    /**
     * AddSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("add", "/coinflip add");
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
            return;
        }
        if(Nexus::getInstance()->getPlayerManager()->getGambleHandler()->getCoinFlip($sender) !== null) {
            $sender->sendMessage(Translation::getMessage("existingCoinFlip"));
            return;
        }
        $sender->sendForm(new CoinFlipCreateForm($sender));
    }
}