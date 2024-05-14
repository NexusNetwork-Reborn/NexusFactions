<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Gamble\Task;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Gamble\GambleHandler;
use Xekvern\Core\Utils\Utils;

class DrawLotteryTask extends Task {

    /** @var GambleHandler */
    private $manager;

    /** @var int */
    private $time = 1800;

    /**
     * DrawLotteryTask constructor.
     *
     * @param GambleHandler $manager
     */
    public function __construct(GambleHandler $manager) {
        $this->manager = $manager;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(): void {
        $hours = floor($this->time / 3600);
        $minutes = (int)($this->time / 60) % 60;
        $seconds = $this->time % 60;
        $total = $this->manager->getTotalDraws() * GambleHandler::TICKET_PRICE;
        if((($minutes % 5 == 0 or $minutes <= 5) and $seconds == 0)) { //  or ($minutes == 0 and $seconds <= 5)
            $message = implode(TextFormat::RESET . "\n", [
                Utils::centerAlignText(TextFormat::BOLD . TextFormat::AQUA . "LOTTERY", 58),
                Utils::centerAlignText(TextFormat::WHITE . "Drawing an amount of: " . TextFormat::LIGHT_PURPLE . "$" . number_format($total), 58),
                Utils::centerAlignText(TextFormat::WHITE . "Time: " . TextFormat::DARK_AQUA . Utils::secondsToTime($this->time), 58),
            ]);
            Server::getInstance()->broadcastMessage($message);
        }
        if($hours < 1) {
            if($minutes == 0 and $seconds == 0) {
                $winner = $this->manager->draw();
                if($winner === null) {
                    $message = implode(TextFormat::RESET . "\n", [
                        Utils::centerAlignText(TextFormat::BOLD . TextFormat::AQUA . "LOTTERY", 58),
                        Utils::centerAlignText(TextFormat::WHITE . "No one has won the lottery.", 58),
                    ]);
                    Server::getInstance()->broadcastMessage($message);
                }
                else {
                    $player = Server::getInstance()->getPlayerExact($winner);
                    if($player instanceof NexusPlayer) {
                        $player->getDataSession()->addToBalance($total);
                    }
                    else {
                        $stmt = Nexus::getInstance()->getMySQLProvider()->getDatabase()->prepare("UPDATE stats SET balance = balance + ? WHERE username = ?");
                        $stmt->bind_param("is", $total, $winner);
                        $stmt->execute();
                        $stmt->close();
                    }
                    $tickets = $this->manager->getPot()[$winner];
                    $percentage = $tickets / $this->manager->getTotalDraws();
                    $percentage = round($percentage * 100, 2);
                    $message = implode(TextFormat::RESET . "\n", [
                        Utils::centerAlignText(TextFormat::BOLD . TextFormat::AQUA . "LOTTERY", 58),
                        Utils::centerAlignText(TextFormat::WHITE . "The Lottery has ended and there is a winner!", 58),
                        Utils::centerAlignText(TextFormat::WHITE . "Winner: " . TextFormat::YELLOW . $winner, 58),
                        Utils::centerAlignText(TextFormat::WHITE . "Amount: " . TextFormat::LIGHT_PURPLE . "$" . number_format($total), 58),
                        Utils::centerAlignText(TextFormat::WHITE . "Tickets Bought: " . TextFormat::YELLOW . $tickets . "tickets($percentage%)!", 58),
                    ]);
                    Server::getInstance()->broadcastMessage($message);
                }
                $this->getHandler()->cancel();
            }
        }
        $this->time--;
    }

    /**
     * @return int
     */
    public function getTimeLeft(): int {
        return $this->time;
    }
}