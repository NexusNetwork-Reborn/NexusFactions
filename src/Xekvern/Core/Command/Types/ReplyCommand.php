<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Args\TextArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ClickSound;

class ReplyCommand extends Command {

    /**
     * ReplyCommand constructor.
     */
    public function __construct() {
        parent::__construct("reply", "Reply to a player.", "/reply <message: string>", ["r"]);
        $this->registerArgument(0, new TextArgument("message"));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(count($args) < 1) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        if(!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $lastTalked = $sender->getLastTalked();
        if(!$lastTalked instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("invalidPlayer"));           
            return;
        }
        if(!$lastTalked->isTakingPMs()) {
            $sender->sendMessage(Translation::getMessage("notTakingPrivateMessages"));             
            return;
        }
        $message = implode(" ", $args);
        $sender->sendMessage(TextFormat::GRAY . "(Message) " . TextFormat::DARK_GRAY . "[" . TextFormat::LIGHT_PURPLE . $lastTalked->getName() . TextFormat::DARK_GRAY . "] " . TextFormat::RESET . TextFormat::YELLOW . $message);
        $lastTalked->sendMessage(TextFormat::GRAY . "(Message) " . TextFormat::DARK_GRAY . "[" . TextFormat::AQUA . $sender->getName() . TextFormat::DARK_GRAY . "] " . TextFormat::RESET . TextFormat::YELLOW . $message);
        if($lastTalked instanceof Player){
            $level = $lastTalked->getWorld();
            if($level !== null) {
                $level->addSound($lastTalked->getEyePos(), new ClickSound());
            }
        }
    }
}