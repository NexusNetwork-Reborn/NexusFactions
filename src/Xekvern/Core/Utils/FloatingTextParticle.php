<?php

declare(strict_types=1);

namespace Xekvern\Core\Utils;

use pocketmine\world\{
    World,
    Position
};
use pocketmine\{
    player\Player,
    Server
};

class FloatingTextParticle extends \pocketmine\world\particle\FloatingTextParticle
{

    /** @var string */
    private $identifier;

    /** @var string */
    private $message;

    /** @var World */
    private $world;

    /** @var Position */
    private $position;

    /**
     * FloatingTextParticle constructor.
     *
     * @param Position $pos
     * @param string $identifier
     * @param string $message
     */
    public function __construct(Position $pos, string $identifier, string $message)
    {
        parent::__construct("", "");
        $this->world = $pos->getWorld();
        $this->identifier = $identifier;
        $this->message = $message;
        $this->position = $pos;
        $this->update();
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return World
     */
    public function getWorld(): World
    {
        return $this->world;
    }

    /**
     * @param null|string $message
     */
    public function update(?string $message = null): void
    {
        $this->message = $message ?? $this->message;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function sendChangesToAll(): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $this->sendChangesTo($player);
        }
    }

    /**
     * @param Player $player
     */
    public function sendChangesTo(Player $player): void
    {
        $this->setTitle($this->message);
        $world = $player->getWorld();
        if ($world === null) {
            return;
        }
        if ($this->world->getDisplayName() !== $world->getDisplayName()) {
            return;
        }
        $this->world->addParticle($this->position, $this, [$player]);
    }

    /**
     * @param Player $player
     */
    public function spawn(Player $player): void
    {
        $this->setInvisible(false);
        $world = $player->getWorld();
        if ($world === null) {
            return;
        }
        $this->world->addParticle($this->position, $this, [$player]);
    }

    /**
     * @param Player $player
     */
    public function despawn(Player $player): void
    {
        $this->setInvisible(true);
        $world = $player->getWorld();
        if ($world === null) {
            return;
        }
        $this->world->addParticle($this->position, $this, [$player]);
    }
}
