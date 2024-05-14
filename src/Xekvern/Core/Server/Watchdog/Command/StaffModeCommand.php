<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Watchdog\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Translation\Translation;

class StaffModeCommand extends Command
{

	/**
	 * StaffChatCommand constructor.
	 */
	public function __construct()
	{
		parent::__construct("staffmode", "Toggle staff mode.", "/staffmode", ["sm"]);
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param array         $args
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void
	{
		if ((!$sender instanceof NexusPlayer) or (!$sender->hasPermission("permission.staff"))) {
			$sender->sendMessage(Translation::getMessage("noPermission"));
			return;
		}
		$sender->setStaffMode(!$sender->isInStaffMode());
		$sender->sendMessage($sender->isInStaffMode() ? Translation::GREEN . "StaffMode: " . TextFormat::GREEN . "ON" :  Translation::RED . "StaffMode: " . TextFormat::RED . "OFF");
		$pk = new GameRulesChangedPacket();
		$pk->gameRules = ["showcoordinates" => new BoolGameRule(false, false)];
		$sender->getNetworkSession()->sendDataPacket($pk);
	}
}
