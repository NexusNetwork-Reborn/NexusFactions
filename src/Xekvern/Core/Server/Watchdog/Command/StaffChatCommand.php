<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Watchdog\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class StaffChatCommand extends Command
{

    /**
     * StaffChatCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("staffchat", "Toggle staff chat.", "/staffchat", ["sc"]);
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
        if ((!$sender instanceof NexusPlayer) or (!$sender->hasPermission("permission.staff"))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $mode = NexusPlayer::PUBLIC;
        if ($sender->getChatMode() !== NexusPlayer::STAFF) {
            $mode = NexusPlayer::STAFF;
        }
        $sender->setChatMode($mode);
        $sender->sendMessage(Translation::getMessage("chatModeSwitch", [
            "mode" =>  TextFormat::GREEN . strtoupper($sender->getChatModeToString())
        ]));
    }
}
