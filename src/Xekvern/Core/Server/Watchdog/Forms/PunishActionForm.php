<?php

namespace Xekvern\Core\Server\Watchdog\Forms;

use Xekvern\Core\Nexus;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use Xekvern\Core\Server\Watchdog\PunishmentEntry;
use Xekvern\Core\Server\Watchdog\Reasons;
use Xekvern\Core\Server\Watchdog\WatchdogException;
use libs\form\CustomForm;
use libs\form\CustomFormResponse;
use libs\form\element\Dropdown;
use libs\form\element\Input;
use libs\form\element\Label;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Utils\Utils;

class PunishActionForm extends CustomForm
{

    const TYPE_TO_REASONS_MAP = [
        PunishmentEntry::MUTE => [
            Reasons::STAFF_DISRESPECT,
            Reasons::SPAMMING
        ],
        PunishmentEntry::BAN => [
            Reasons::ADVERTISING,
            Reasons::EXPLOITING,
            Reasons::IRL_SCAMMING,
            Reasons::ALTING,
            Reasons::BAN_EVADING,
            Reasons::DDOS_THREATS,
            Reasons::HACK
        ]
    ];

    /** @var int */
    private $type;

    /**
     * PunishActionForm constructor.
     *
     * @param int $type
     */
    public function __construct(int $type)
    {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Punish";
        $this->type = $type;
        $elements = [];
        $elements[] = new Label("Label", "The expiration time will automatically be chosen due to how many offenses a player has.");
        $elements[] = new Input("Name", "Username", "Donald Trump");
        $elements[] = new Dropdown("Reasons", "Reasons", self::TYPE_TO_REASONS_MAP[$type]);
        parent::__construct($title, $elements);
    }

    /**
     * @param Player $player
     * @param CustomFormResponse $data
     *
     * @throws TranslatonException
     * @throws WatchdogException
     */
    public function onSubmit(Player $player, CustomFormResponse $data): void
    {
        $name = $data->getString("Name");
        /** @var Dropdown $element */
        $element = $this->getElementByName("Reasons");
        $reason = $element->getOption($data->getInt("Reasons"));
        switch ($this->type) {
            case PunishmentEntry::BAN:
                if (Nexus::getInstance()->getServerManager()->getWatchdogHandler()->isBanned($name)) {
                    $player->sendMessage(Translation::getMessage("alreadyBanned", [
                        "name" => TextFormat::YELLOW . $name,
                    ]));
                    return;
                }
                break;
            case PunishmentEntry::MUTE:
                if (Nexus::getInstance()->getServerManager()->getWatchdogHandler()->isMuted($name)) {
                    $player->sendMessage(Translation::getMessage("alreadyMuted", [
                        "name" => TextFormat::YELLOW . $name,
                    ]));
                    return;
                }
                break;
        }
        if (Nexus::getInstance()->getServerManager()->getWatchdogHandler()->isMuted($name)) {
            Nexus::getInstance()->getServerManager()->getWatchdogHandler()->relieve(Nexus::getInstance()->getServerManager()->getWatchdogHandler()->getMute($name), $player->getName());
        }
        if (Nexus::getInstance()->getServerManager()->getWatchdogHandler()->isBanned($name)) {
            Nexus::getInstance()->getServerManager()->getWatchdogHandler()->relieve(Nexus::getInstance()->getServerManager()->getWatchdogHandler()->getBan($name), $player->getName());
        }
        $entry = Nexus::getInstance()->getServerManager()->getWatchdogHandler()->punish($name, $this->type, $player->getName(), $reason);
        if ($entry->getExpiration() === 0) {
            $expiration = "Forever";
        } else {
            $expiration = Utils::secondsToTime($entry->getExpiration());
        }
        switch ($this->type) {
            case PunishmentEntry::BAN:
                $banned = Server::getInstance()->getPlayerByPrefix($name);
                if ($banned !== null) {
                    $name = $banned->getName();
                    $banned->close();
                }
                Server::getInstance()->broadcastMessage(Translation::getMessage("banBroadcast", [
                    "name" => TextFormat::RED . $name,
                    "effector" => TextFormat::DARK_RED . $player->getName(),
                    "reason" => TextFormat::YELLOW . "\"$reason\"",
                    "time" => TextFormat::RED . $expiration
                ]));
                break;
            case PunishmentEntry::MUTE:
                Server::getInstance()->broadcastMessage(Translation::getMessage("muteBroadcast", [
                    "name" => TextFormat::RED . $name,
                    "effector" => TextFormat::DARK_RED . $player->getName(),
                    "reason" => TextFormat::YELLOW . "\"$reason\"",
                    "time" => TextFormat::RED . $expiration
                ]));
                break;
        }
    }
}