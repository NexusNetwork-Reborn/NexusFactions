<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Kit;

use Xekvern\Core\Server\Kit\Types\Once;
use Xekvern\Core\Server\Kit\Types\Starter;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Kit\Types\Deity;
use Xekvern\Core\Server\Kit\Types\Hoplite;
use Xekvern\Core\Server\Kit\Types\King;
use Xekvern\Core\Server\Kit\Types\Knight;
use Xekvern\Core\Server\Kit\Types\Prince;
use Xekvern\Core\Server\Kit\Types\Sacred\Archer;
use Xekvern\Core\Server\Kit\Types\Sacred\Assassin;
use Xekvern\Core\Server\Kit\Types\Sacred\Bandit;
use Xekvern\Core\Server\Kit\Types\Sacred\Miner;
use Xekvern\Core\Server\Kit\Types\Sacred\Overlord;
use Xekvern\Core\Server\Kit\Types\Sacred\Raider;
use Xekvern\Core\Server\Kit\Types\Sacred\Reaper;
use Xekvern\Core\Server\Kit\Types\Sacred\Warlord;
use Xekvern\Core\Server\Kit\Types\Spartan;
use Xekvern\Core\Server\Kit\Types\Subordinate;

class KitHandler {

    /** @var Nexus */
    private $core;

    /** @var Kit[] */
    private $kits = [];

    /** @var SacredKit[] */
    private $sacredKits = [];

    /**
     * KitHandler constructor.
     *
     * @param Nexus $core
     *
     * @throws KitException
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $this->init();
    }

    /**
     * @throws KitException
     */
    public function init(): void {
        $this->addKit(new Starter());
        $this->addKit(new Once());
        $this->addKit(new Subordinate());
        $this->addKit(new Knight());
        $this->addKit(new Hoplite());
        $this->addKit(new Prince());    
        $this->addKit(new Spartan());
        $this->addKit(new King());
        $this->addKit(new Deity());
        $this->addKit(new Miner());
        $this->addKit(new Archer());
        $this->addKit(new Raider());
        $this->addKit(new Bandit());
        $this->addKit(new Warlord());
        $this->addKit(new Overlord());
        $this->addKit(new Assassin());
        $this->addKit(new Reaper());
    }

    /**
     * @param string $kit
     *
     * @return Kit|SacredKit|null
     */
    public function getKitByName(string $kit) : ?Kit {
        return $this->kits[$kit] ?? $this->sacredKits[$kit] ?? null;
    }

    /**
     * @return Kit[]
     */
    public function getKits(): array {
        return $this->kits;
    }

    /**
     * @return SacredKit[]
     */
    public function getSacredKits(): array {
        return $this->sacredKits;
    }

    /**
     * @param Kit $kit
     *
     * @throws KitException
     */
    public function addKit(Kit $kit) : void {
        if(isset($this->kits[$kit->getName()])) {
            throw new KitException("Attempted to override a kit with the name of \"{$kit->getName()}\" and a class of \"" . get_class($kit) . "\".");
        }
        if(isset($this->sacredKits[$kit->getName()])) {
            throw new KitException("Attempted to override a sacred kit with the name of \"{$kit->getName()}\" and a class of \"" . get_class($kit) . "\".");
        }
        if($kit instanceof SacredKit) {
            $this->sacredKits[$kit->getName()] = $kit;
        }
        else {
            $this->kits[$kit->getName()] = $kit;
        }
    }
}