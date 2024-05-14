<?php

namespace Xekvern\Core\Server\Watchdog;

class PunishmentEntry {

    const BAN = 0;

    const MUTE = 1;

    const BLOCK = 2;

    /** @var string */
    private $username;

    /** @var int */
    private $type;

    /** @var int */
    private $expiration;

    /** @var int */
    private $time;

    /** @var string */
    private $effector;

    /** @var string */
    private $reason;

    /**
     * PunishmentEntry constructor.
     *
     * @param string $username
     * @param int $type
     * @param int $expiration
     * @param int $time
     * @param string $effector
     * @param string $reason
     */
    public function __construct(string $username, int $type, int $expiration, int $time, string $effector, string $reason) {
        $this->username = $username;
        $this->type = $type;
        $this->expiration = $expiration;
        $this->time = $time;
        $this->effector = $effector;
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getUsername(): string {
        return $this->username;
    }

    /**
     * @return int
     */
    public function getType(): int {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getExpiration(): int {
        return $this->expiration;
    }

    /**
     * @param int $expiration
     */
    public function addExpiration(int $expiration): void {
        if ($this->expiration === 0) {
            return;
        }
        $this->expiration += $expiration;
    }

    /**
     * @return int
     */
    public function getTime(): int {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getEffector(): string {
        return $this->effector;
    }

    /**
     * @return string
     */
    public function getReason(): string {
        return $this->reason;
    }

    /**
     * @return bool
     */
    public function check(): bool {
        if ($this->expiration === 0) {
            return true;
        }
        return time() < ($this->time + $this->expiration);
    }
}