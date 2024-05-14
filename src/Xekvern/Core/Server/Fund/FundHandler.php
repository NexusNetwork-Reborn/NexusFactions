<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Fund;

use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Fund\Utils\FundConfigable;
use Xekvern\Core\Server\Fund\Utils\FundInformation;
use Xekvern\Core\Server\Fund\Utils\MergedFund;

class FundHandler
{

    /** @var Nexus */
    private $core;

    /** @var string */
    private string $file;
    /** @var FundInformation[] */
    private array $funds = [];
    /** @var FundConfigable[] */
    private array $fund_configs = [];

    /**
     * FundHandler constructor. 
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
        $this->core->saveDefaultConfig();
        $this->file = $core->getDataFolder() . DIRECTORY_SEPARATOR . "funds.json";
        if(!file_exists($this->file)) {
            file_put_contents($this->file, json_encode([]));
        }
        foreach(json_decode(file_get_contents($this->file), true) as $data) {
            $this->funds[$data["name"]] = FundInformation::fromMap($data);
        }
        $config = $core->getConfig();
        foreach ($config->get("funds", []) as $fund) {
            $fundName = $fund["name"];
            $fundType = $fund["type"];
            $fundGoal = $fund["goal"];
            if (!isset($this->funds[$fundName])) {
                $this->addFund($fundName, 0);
                $core->getServer()->getLogger()->info("Fund '$fundName' has been added to the funds list.");
            }
            $this->fund_configs[$fundName] = FundConfigable::make($fundName, $fundType, $fundGoal);
        }
      //  foreach($this->getAllMergeFunds() as $merged_fund) {
           // if ($merged_fund == null) {
               // $core->getLogger()->error("The fund '{$merged_fund->getFundConfigable()->getName()}' does not exist.");
                //return;
           // }
            //$funded_message = $merged_fund->isFunded() ? "has" : "has not";
            //$core->getServer()->getLogger()->info(TextFormat::GOLD . "The fund '{$merged_fund->getFundConfigable()->getName()}' $funded_message reached its goal.");
        //}
    }

    public function addFund(...$args): void
    {
        $this->funds[] = FundInformation::make(...$args);
    }

    public function getFundConfig(string $name): ?FundConfigable
    {
        return $this->fund_configs[$name] ?? null;
    }

    public function getFund(string $name): ?FundInformation
    {
        return $this->funds[$name] ?? null;
    }

    public function getFunds()
    {
        return $this->funds;
    }

    public function getMergeFund(string $name): ?MergedFund
    {
        $fund = $this->getFund($name);
        $config = $this->getFundConfig($name);
        if ($fund === null || $config === null) {
            return null;
        }
        return MergedFund::make($fund, $config);
    }

    public function getAllMergeFunds(): array
    {
        $mergedFunds = [];
        foreach ($this->funds as $fundName => $data) {
            $fundInformation = $this->getFund($fundName);
            $config = $this->getFundConfig($fundName);
            if ($config !== null) {
                $mergedFunds[] = MergedFund::make($fundInformation, $config);
            }
        }
        return $mergedFunds;
    }

    public function save(string $name, FundInformation $fund): void
    {
        $this->funds[$name] = $fund;
        $data = json_decode(file_get_contents($this->file), true);
        $data[$name] = FundInformation::toMap($fund);
        file_put_contents($this->file, json_encode($data));
    }
}
