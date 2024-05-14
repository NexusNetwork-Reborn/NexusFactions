<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Watchdog\Task;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;

class ProxyCheckTask extends AsyncTask {

    const URL = "http://v2.api.iphub.info/ip/{ADDRESS}";

    /** @var string */
    private $player;

    /** @var string */
    private $address;

    /** @var string */
    private $key;

    /**
     * ProxyCheckTask constructor.
     *
     * @param string $player
     * @param string $address
     * @param string $key
     */
    public function __construct(string $player, string $address, string $key) {
        $this->player = $player;
        $this->address = $address;
        $this->key = $key;
        Nexus::getInstance()->getLogger()->notice("Unknown ip detected in $player, checking for a vpn or proxy now.");
    }

    public function onRun(): void {
        $url = str_replace("{ADDRESS}", $this->address, self::URL);
        $get = Internet::getURL($url, 10, ["X-Key: $this->key"]);
        if($get === false) {
            $this->setResult($get);
            return;
        }
        $get = json_decode($get->getBody(), true);
        if(!is_array($get)) {
            $this->setResult(false);
            return;
        }
        $result = $get["block"];
        $this->setResult($result);
        return;
    }

    /**
     * @param Server $server
     */
    public function onCompletion(): void {
        $core = Nexus::getInstance();
        $server = $core->getServer();
        $player = $server->getPlayerExact($this->player);
        if($player === null or (!$player->isOnline())) {
            return;
        }
        $result = $this->getResult();
        switch($result) {
            case 0:
                $core->getLogger()->info("No malicious ip swapper was detected in {$this->player}.");
                $uuid = $player->getUniqueId()->toString();
                $stmt = Nexus::getInstance()->getMySQLProvider()->getDatabase()->prepare("INSERT INTO ipAddress(uuid, username, ipAddress, riskLevel) VALUES(?, ?, ?, ?)");
                $stmt->bind_param("sssi", $uuid, $this->player, $this->address, $result);
                $stmt->execute();
                $stmt->close();
                break;
            case 1:
                $core->getLogger()->warning("A malicious ip swapper was detected in {$this->player}.");
                $uuid = $player->getUniqueId()->toString();
                $stmt = Nexus::getInstance()->getMySQLProvider()->getDatabase()->prepare("INSERT INTO ipAddress(uuid, username, ipAddress, riskLevel) VALUES(?, ?, ?, ?)");
                $stmt->bind_param("sssi", $uuid, $this->player, $this->address, $result);
                $stmt->execute();
                $stmt->close();
                if(!$player instanceof NexusPlayer) {
                    return;
                }
                $player->close(null, TextFormat::RED . "A malicious ip swapper was detected!");
                break;
            case 2:
                $uuid = $player->getUniqueId()->toString();
                $stmt = Nexus::getInstance()->getMySQLProvider()->getDatabase()->prepare("INSERT INTO ipAddress(uuid, username, ipAddress, riskLevel) VALUES(?, ?, ?, ?)");
                $stmt->bind_param("sssi", $uuid, $this->player, $this->address, $result);
                $stmt->execute();
                $stmt->close();
                $core->getLogger()->info("No malicious ip swapper was detected in {$this->player} but could potentially be using one.");
                break;
            default:
                $core->getLogger()->warning("Error in checking {$this->player}'s proxy.");
                if(!$player instanceof NexusPlayer) {
                    return;
                }
                $player->close(null, TextFormat::RED . "An ip check was conducted and had failed. Please rejoin to complete this check.");
        }
    }
}