<?php

namespace Xekvern\Core\Provider;

use mysqli;

class MySQLCredentials {

    /** @var string */
    private $host;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $database;

    /** @var int */
    private $port;

    /**
     * MySQLCredentials constructor.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $database
     * @param int $port
     */
    public function __construct(string $host, string $username, string $password, string $database, int $port = 3306) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getHost(): string {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getUsername(): string {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getDatabase(): string {
        return $this->database;
    }

    /**
     * @return int
     */
    public function getPort(): int {
        return $this->port;
    }

    /**
     * @return mysqli
     */
    public function createInstance(): mysqli {
        return new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);
    }
}