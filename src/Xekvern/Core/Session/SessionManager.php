<?php

namespace Xekvern\Core\Session;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Session\Types\CESession;
use Xekvern\Core\Session\Types\DataSession;

class SessionManager
{

    /** @var Nexus */
    private $core;

    /** @var DataSession[] */
    private $sessions = [];

    /** @var CESession[] */
    private $ceSessions = [];

    /**
     * SessionManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
    }

    /**
     * @param NexusPlayer $player
     *
     * @return DataSession
     */
    public function getSession(NexusPlayer $player): DataSession
    {
        if (isset($this->sessions[$player->getUniqueId()->toString()])) {
            $this->sessions[$player->getUniqueId()->toString()]->setOwner($player);
            return $this->sessions[$player->getUniqueId()->toString()];
        }
        $session = new DataSession($player);
        $this->sessions[$player->getUniqueId()->toString()] = $session;
        return $session;
    }

    /**
     * @param DataSession $session
     */
    public function setSession(DataSession $session): void
    {
        $this->sessions[$session->getOwner()->getUniqueId()->toString()] = $session;
    }


    /**
     * @param NexusPlayer $player
     *
     * @return CESession
     */
    public function getCESession(NexusPlayer $player): CESession
    {
        if (isset($this->ceSessions[$player->getUniqueId()->toString()])) {
            $this->ceSessions[$player->getUniqueId()->toString()]->setOwner($player);
            $this->ceSessions[$player->getUniqueId()->toString()]->reset();
            return $this->ceSessions[$player->getUniqueId()->toString()];
        }
        $session = new CESession($player);
        $this->ceSessions[$player->getUniqueId()->toString()] = $session;
        return $session;
    }

    /**
     * @param CESession $session
     */
    public function setCESession(CESession $session): void
    {
        $this->ceSessions[$session->getOwner()->getUniqueId()->toString()] = $session;
    }
}
