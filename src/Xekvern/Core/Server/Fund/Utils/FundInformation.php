<?php

namespace Xekvern\Core\Server\Fund\Utils;

use pocketmine\Server;
use Xekvern\Core\Nexus;

final class FundInformation
{
    public function __construct(
        private readonly string $name,
        private int    $amount,
    ) {
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;

        $config = Nexus::getInstance()->getServerManager()->getFundHandler()->getMergeFund($this->getName());
        if ($amount >= $config->getFundConfigable()->getGoal()) {
            Server::getInstance()->broadcastMessage("The fund '{$this->getName()}' has reached its goal!");
        }
        Nexus::getInstance()->getServerManager()->getFundHandler()->save($this->getName(), $this);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public static function make(string $name, int $amount): FundInformation
    {
        return new FundInformation($name, $amount);
    }

    public static function toMap(FundInformation $fund): array
    {
        return [
            "name" => $fund->getName(),
            "amount" => $fund->getAmount(),
        ];
    }

    public static function fromMap(array $map): FundInformation
    {
        return FundInformation::make(
            $map["name"],
            $map["amount"],
        );
    }
}
