<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Args\TextArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ClickSound;

class TellCommand extends Command {

    /**
     * TellCommand constructor.
     */
    public function __construct() {
        parent::__construct("tell", "Send a message to a player.", "/tell <player:target> <message: string>", ["whisper, w, message, msg"]);
        $this->registerArgument(0, new TargetArgument("player"));
        $this->registerArgument(1, new TextArgument("message"));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(count($args) < 2) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $name = array_shift($args);
        /** @var NexusPlayer $player */
        $player = $this->getCore()->getServer()->getPlayerExact($name);
        if($sender === $player or $player === null) {
            $sender->sendMessage(Translation::getMessage("invalidPlayer"));
            return;
        }
        if(!$player->isTakingPMs()) {
            $sender->sendMessage(Translation::getMessage("notTakingPrivateMessages"));
            return;
        }
        $message = implode(" ", $args);
        $sender->sendMessage(TextFormat::GRAY . "(Message) " . TextFormat::DARK_GRAY . "[" . TextFormat::LIGHT_PURPLE . $player->getName() . TextFormat::DARK_GRAY . "] " . TextFormat::RESET . TextFormat::YELLOW . $message);
        $player->sendMessage(TextFormat::GRAY . "(Message) " . TextFormat::DARK_GRAY . "[" . TextFormat::AQUA . $sender->getName() . TextFormat::DARK_GRAY . "] " . TextFormat::RESET . TextFormat::YELLOW . $message);
        $level = $player->getWorld();
        if($level !== null) {
            $level->addSound($player->getEyePos(), new ClickSound());
        }
        if($player instanceof NexusPlayer) {
            $player->setLastTalked($sender);
        }
        if($sender instanceof NexusPlayer) {
            $sender->setLastTalked($player);
        }
    }
}