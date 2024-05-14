<?php

namespace Xekvern\Core\Server\Watchdog\Task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class PunishmentLogTask extends AsyncTask {

    /** @var string */
    private $curlopts;

    /**
     * PunishmentLogTask constructor.
     *
     * @param string $message
     */
    public function __construct(string $message) {
        $this->curlopts = serialize($curlopts = [
            'content' => $message,
            'username' => null
        ]);
    }

    public function onRun(): void {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://discord.com/api/webhooks/1203167927390707732/oW4Iw12DxdeEhquPpuNiGT4XTncGJRHVrkJwp7Y3bf1MFXx-s0k-xPbVA4B0tZ-B1vRg");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(unserialize($this->curlopts)));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        $curlerror = curl_error($curl);
        $responsejson = json_decode($response, true);
        $success = false;
        $error = "IDK What happened";
        if($curlerror != "") {
            $error = $curlerror;
        }
        elseif(curl_getinfo($curl, CURLINFO_HTTP_CODE) != 204) {
            $error = $responsejson["message"];
        }
        elseif(curl_getinfo($curl, CURLINFO_HTTP_CODE) == 204 OR $response === "") {
            $success = true;
        }
        $result = ["Response" => $response, "Error" => $error, "success" => $success];
        $this->setResult($result);
    }

    /**
     * @param Server $server
     */
    public function onCompletion(): void {
    }
}