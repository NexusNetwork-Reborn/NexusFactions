<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Announcement\Task;

use Xekvern\Core\Nexus;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslationException;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ClickSound;

class BroadcastMessagesTask extends Task {

    /** @var Nexus */
    private $core;

    /**
     * BroadcastMessagesTask constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @param int $currentTick
     *
     * @throws TranslationException
     */
    public function onRun(): void {
        $message = $this->core->getServerManager()->getAnnouncementHandler()->getNextMessage();
        $this->core->getServer()->broadcastMessage(TextFormat::GRAY . "[" . TextFormat::LIGHT_PURPLE . "Notice" . TextFormat::GRAY . "] " . TextFormat::RESET . TextFormat::WHITE . $message);
        foreach(Nexus::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $player->broadcastSound(new ClickSound(), [$player]);
        }
    }
}