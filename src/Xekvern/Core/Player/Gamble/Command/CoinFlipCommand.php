<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Gamble\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\Gamble\Command\Forms\CoinFlipListForm;
use Xekvern\Core\Player\Gamble\Command\SubCommands\CancelSubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Gamble\Command\SubCommands\AddSubCommand;

class CoinFlipCommand extends Command
{

    /**
     * CoinFlipCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("coinflip", "Manage coin flipping", "/coinflip", ["cf"]);
        $this->addSubCommand(new AddSubCommand());
        $this->addSubCommand(new CancelSubCommand());
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
        $fundHandler = Nexus::getInstance()->getServerManager()->getFundHandler();
        if ($fundHandler->getMergeFund("Coin Flips")->isFunded() === false) {
            $sender->sendMessage(Translation::getMessage("notReachedGoal"));
            return;
        }
        if (isset($args[0])) {
            $subCommand = $this->getSubCommand($args[0]);
            if ($subCommand !== null) {
                $subCommand->execute($sender, $commandLabel, $args);
                return;
            }
            return;
        }
        $sender->sendForm(new CoinFlipListForm($sender));
    }
}