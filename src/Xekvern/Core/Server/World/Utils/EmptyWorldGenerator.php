<?php

namespace Xekvern\Core\Server\World\Utils;

use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;

class EmptyWorldGenerator extends Generator {

    public function __construct(int $seed, string $preset) {
        parent::__construct($seed, $preset);
    }

    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
    }
}