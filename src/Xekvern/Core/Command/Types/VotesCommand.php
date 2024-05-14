<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class VotesCommand extends Command {

    /**
     * VotesCommand constructor.
     */
    public function __construct() {
        parent::__construct("votes", "Check amount of votes until sacred all!", "/votes", ["votes"]);
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
        $votes = $this->getCore()->getVotes();
        $factor = (150 * ceil($votes / 150)) - $votes;
        $sender->sendMessage(Translation::BLUE . TextFormat::AQUA . $factor . TextFormat::GRAY . " votes until sacred all!");
    }
}