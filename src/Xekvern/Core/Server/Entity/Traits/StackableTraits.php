<?php

namespace Xekvern\Core\Server\Entity\Traits;

trait StackableTraits
{

    protected int $maxStack = 300;
    protected int $stack = 1;

    public function canStack(): Bool
    {
        return $this->stack < $this->maxStack;
    }

    public function getMaxStackSize(): Int
    {
        return $this->maxStack;
    }

    public function setMaxStackSize(Int $stack): Void
    {
        $this->maxStack = $stack < 1 ? 1 : $stack;
    }

    public function getStackSize(): Int
    {
        return $this->stack;
    }

    public function setStackSize(Int $stack): Void
    {
        if ($stack > $this->maxStack) {
            $stack = $this->maxStack;
        }
        $this->stack = $stack < 0 ? 0 : $stack;
    }

    public function addStackSize(Int $stack): Void
    {
        $this->setStackSize($this->stack + $stack);
    }

    public function reduceStackSize(Int $stack): Void
    {
        $this->setStackSize($this->stack - $stack);
    }
}
