<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\muqsit\arithmexp\Parser;
use libs\NhanAZ\libBedrock\Sounder;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;

class CalculateCommand extends Command {

    /**
     * CalculateCommand constructor.
     */
    public function __construct() {
        parent::__construct("calculate", "Show result of a calculation", "/calculate <calculation:string>", ["calc","calculator"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!isset($args[0])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $prefix = "&f[&3Nexus&bCalculator&f]&r";
		try {
			$parser = Parser::createDefault();
			$expression = implode(" ", $args);
			$expression = $parser->parse($expression);
			$result = $expression->evaluate();
			$result = str_replace(["{prefix}", "{result}"], [$prefix, $result], "{prefix} &aResult: &b{result}");
			$sender->sendMessage(TextFormat::colorize($result));
            if($sender instanceof NexusPlayer) {
                $sender->playSound("mob.villager.yes", 1, 1);
            }
		} catch (\Throwable $e) {
			$error = str_replace(["{prefix}", "{error}"], [$prefix, $e->getMessage()], "{prefix} &cError: {error}");
			$sender->sendMessage(TextFormat::colorize($error));
            if($sender instanceof NexusPlayer) {
                $sender->playSound("mob.villager.no", 1, 1);
            }
		}
		return;
    }
}