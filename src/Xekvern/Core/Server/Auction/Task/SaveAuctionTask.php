<?php

namespace Xekvern\Core\Server\Auction\Task;

use pocketmine\scheduler\AsyncTask;

class SaveAuctionTask extends AsyncTask
{
    private string $data;

    /**
     * SaveAuctionTask constructor.
     *
     * @param string $path
     * @param array $data
     */
    public function __construct(private string $path, array $data) {
        $this->data = serialize($data);
    }

    /**
     * @param int $currentTick
     */
    public function onRun(): void {
        file_put_contents($this->path, $this->data);
    }
}
