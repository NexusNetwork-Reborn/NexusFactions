<?php

declare(strict_types=1);

namespace Xekvern\Core\Utils;

class Selector
{

    /** @var int */
    protected $currentKey;

    /** @var string[] */
    protected $values = [];

    /**
     * Selector constructor.
     *
     * @param string[] $values
     * @param int|null $defaultIndex
     */
    public function __construct(array $values, int $defaultIndex = null)
    {
        $this->values = $values;
        if (is_null($defaultIndex)) {
            $this->currentKey = array_rand($values);
        } else {
            $this->currentKey = $defaultIndex;
        }
    }

    public function prev(): void
    {
        if (($this->currentKey - 1) < min(array_keys($this->values))) {
            $this->currentKey = max(array_keys($this->values));
        } else {
            $this->currentKey = $this->currentKey - 1;
        }
    }

    /**
     * @return string
     */
    public function current(): string
    {
        return $this->values[$this->currentKey];
    }

    public function next(): void
    {
        if (($this->currentKey + 1) > max(array_keys($this->values))) {
            $this->currentKey = min(array_keys($this->values));
        } else {
            $this->currentKey = $this->currentKey + 1;
        }
    }

    /**
     * @param int $previousIndex
     *
     * @return string
     */
    public function getPrevious(int $previousIndex = 1): string
    {
        $currentKey = $this->currentKey;
        for ($i = 0; $i < $previousIndex; $i++) {
            if (($currentKey - 1) < min(array_keys($this->values))) {
                $currentKey = max(array_keys($this->values));
            } else {
                $currentKey = $currentKey - 1;
            }
        }
        return $this->values[$currentKey];
    }

    /**
     * @param int $nextIndex
     *
     * @return mixed
     */
    public function getNext(int $nextIndex = 1): string
    {
        $currentKey = $this->currentKey;
        for ($i = 0; $i < $nextIndex; $i++) {
            if (($currentKey + 1) > max(array_keys($this->values))) {
                $currentKey = min(array_keys($this->values));
            } else {
                $currentKey = $currentKey + 1;
            }
        }
        return $this->values[$currentKey];
    }

    /**
     * @return int
     */
    public function getCurrentKey(): int
    {
        return $this->currentKey;
    }

    /**
     * @return string[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param $value
     * @param int|null $defaultIndex
     */
    public function addValue($value, int $defaultIndex = null): void
    {
        $this->values[] = $value;
        if (is_null($defaultIndex)) {
            $this->currentKey = array_rand($this->values);
        } else {
            $this->currentKey = $defaultIndex;
        }
    }

    /**
     * @param int|null $index
     */
    public function setIndex(int $index = null): void
    {
        if (is_null($index)) {
            $this->currentKey = array_rand($this->values);
        } else {
            $this->currentKey = $index;
        }
    }
}
