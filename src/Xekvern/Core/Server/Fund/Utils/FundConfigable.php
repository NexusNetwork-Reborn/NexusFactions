<?php

namespace Xekvern\Core\Server\Fund\Utils;

final class FundConfigable
{
    public function __construct(
        readonly string $name,
        readonly string $type,
        readonly int    $goal
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getGoal(): int
    {
        return $this->goal;
    }

    public static function make(string $name, string $type, int $goal): FundConfigable
    {
        return new FundConfigable($name, $type, $goal);
    }
}
