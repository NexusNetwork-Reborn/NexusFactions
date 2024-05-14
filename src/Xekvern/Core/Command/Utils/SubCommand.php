<?php
declare(strict_types=1);

namespace Xekvern\Core\Command\Utils;

use Xekvern\Core\Nexus;
use pocketmine\command\CommandSender;

abstract class SubCommand implements IArgumentable {
    use ArgumentableTrait;

    /** @var string */
    private $name;

    /** @var string */
    private $usage;

    /** @var string[] */
    private $aliases;

    /**
     * SubCommand constructor.
     *
     * @param string $name
     * @param string|null $usage
     * @param string[] $aliases
     */
    public function __construct(string $name, ?string $usage = null, array $aliases = []) {
        $this->name = $name;
        $this->usage = $usage;
        $this->aliases = $aliases;
    }

    /**
     * @return Nexus
     */
    public function getCore(): Nexus {
        return Nexus::getInstance();
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getUsage(): ?string {
        return $this->usage;
    }

    /**
     * @return string[]
     */
    public function getAliases(): array {
        return $this->aliases;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    abstract public function execute(CommandSender $sender, string $commandLabel, array $args): void;
}