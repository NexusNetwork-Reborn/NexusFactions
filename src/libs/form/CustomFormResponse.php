<?php

declare(strict_types = 1);

namespace libs\form;

use InvalidArgumentException;

class CustomFormResponse {

    /** @var array */
    private $data;

    /**
     * CustomFormResponse constructor.
     *
     * @param array $data
     */
    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * @param string $name
     *
     * @return int
     */
    public function getInt(string $name): int {
        $this->checkExists($name);
        return $this->data[$name];
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getString(string $name): string {
        $this->checkExists($name);
        return $this->data[$name];
    }

    /**
     * @param string $name
     *
     * @return float
     */
    public function getFloat(string $name): float {
        $this->checkExists($name);
        return $this->data[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function getBool(string $name): bool {
        $this->checkExists($name);
        return $this->data[$name];
    }

    /**
     * @return array
     */
    public function getAll(): array {
        return $this->data;
    }

    /**
     * @param string $name
     */
    private function checkExists(string $name): void {
        if(!isset($this->data[$name])) {
            throw new InvalidArgumentException("Value \"$name\" not found");
        }
    }
}