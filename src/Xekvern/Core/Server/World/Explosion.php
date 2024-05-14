<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\World;

use pocketmine\world\Explosion as WorldExplosion;

class Explosion extends WorldExplosion {

    /** 
     * @return bool
     */
    public function explodeB(): bool {
        foreach($this->affectedBlocks as $block) {
            //NexusEvents::checkSpawner($block);
        }
        return parent::explodeB();
    }
}