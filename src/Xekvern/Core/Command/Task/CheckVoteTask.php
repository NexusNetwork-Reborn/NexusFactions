<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Task;

use Xekvern\Core\Server\Item\Types\SacredStone;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;

class CheckVoteTask extends AsyncTask {

    const API_KEY = "4TEn71moOyKO3cmS7JaY3cfreinuhsZkcH";

    const STATS_URL = "https://minecraftpocket-servers.com/api/?object=servers&element=detail&key=" . self::API_KEY;

    const CHECK_URL = "http://minecraftpocket-servers.com/api-vrc/?object=votes&element=claim&key=" . self:: API_KEY . "&username={USERNAME}";

    const POST_URL = "http://minecraftpocket-servers.com/api-vrc/?action=post&object=votes&element=claim&key=" . self:: API_KEY . "&username={USERNAME}";

    const VOTED = "voted";

    const CLAIMED = "claimed";

    /** @var string */
    private $player;

    /**
     * CheckVoteTask constructor.
     *
     * @param string $player
     */
    public function __construct(string $player) {
        $this->player = $player;
    }

    public function onRun(): void {
        $result = [];
        $player = str_replace(" ", "%20", $this->player);
        $get = Internet::getURL(str_replace("{USERNAME}", $player, self::CHECK_URL));
        if($get === false) {
            return;
        }
        $get = json_decode($get->getBody(), true);
        if((!isset($get[self::VOTED])) or (!isset($get[self::CLAIMED]))) {
            return;
        }
        $result[self::VOTED] = $get[self::VOTED];
        $result[self::CLAIMED] = $get[self::CLAIMED];
        if($get[self::VOTED] === true and $get[self::CLAIMED] === false) {
            $post = Internet::postURL(str_replace("{USERNAME}", $player, self::POST_URL), []);
            if($post === false) {
                $result = null;
            }
        }
        $this->setResult($result);
    }

    /**
     * @param Server $server
     *
     * @throws TranslatonException
     */
    public function onCompletion(): void {
        $server = Nexus::getInstance()->getServer();
        $player = $server->getPlayerExact($this->player);
        if((!$player instanceof NexusPlayer) or $player->isClosed()) {
            return;
        }
        $result = $this->getResult();
        if(empty($result)) {
            $player->sendMessage(Translation::getMessage("errorOccurred"));
            return;
        }
        $player->setCheckingForVote(false);
        if($result[self::VOTED] === true) {
            if($result[self::CLAIMED] === true) {
                $player->setVoted();
                $player->sendMessage(Translation::getMessage("alreadyVoted"));
                return;
            }
            $player->setVoted();
            $votes = Nexus::getInstance()->getVotes() + 1;
            Nexus::getInstance()->setVotes($votes);
            $keys = (int)ceil($votes / 150) <= 15 ? (int)ceil($votes / 150) : 15;
            $factor = (150 * ceil($votes / 150)) - $votes;
            $server->broadcastMessage(Translation::getMessage("voteBroadcast", [
                "name" => TextFormat::WHITE . $player->getName(),
                "votes" => TextFormat::GREEN . $factor,
                "amount" => TextFormat::RED . "x$keys"
            ]));
            $player->getDataSession()->addVotePoints();
            if($factor <= 0) {
                $item = (new SacredStone())->getItemForm()->setCount($keys);
                $player->getCore()->getServer()->broadcastMessage(Translation::getMessage("sacredStoneAll", [
                    "name" => TextFormat::AQUA . "Voting System",
                    "amount" => TextFormat::YELLOW . $keys,
                ]));
                /** @var NexusPlayer $player */
                foreach($player->getCore()->getServer()->getOnlinePlayers() as $player) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                }
            }
            return;
        }
        $player->playErrorSound();
        $player->sendMessage(Translation::getMessage("haveNotVoted"));
        $player->setVoted(false);
    }
}