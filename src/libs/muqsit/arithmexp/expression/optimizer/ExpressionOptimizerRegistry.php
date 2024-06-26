<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\expression\optimizer;

use InvalidArgumentException;
use libs\muqsit\arithmexp\operator\ChangeListenableTrait;

final class ExpressionOptimizerRegistry{
	use ChangeListenableTrait;

	public static function createDefault() : self{
		$registry = new self();
		$registry->register("reorder", new ReorderExpressionOptimizer());
		$registry->register("operator_strength_reduction", new OperatorStrengthReductionExpressionOptimizer());
		$registry->register("constant_folding", new ConstantFoldingExpressionOptimizer());
		$registry->register("idempotence_folding", new IdempotenceFoldingExpressionOptimizer());
		return $registry;
	}

	/** @var array<string, ExpressionOptimizer> */
	private array $registered = [];

	public function __construct(){
	}

	public function register(string $identifier, ExpressionOptimizer $optimizer) : void{
		$this->registered[$identifier] = $optimizer;
		$this->notifyChangeListener();
	}

	public function get(string $identifier) : ExpressionOptimizer{
		return $this->registered[$identifier] ?? throw new InvalidArgumentException("Expression optimizer \"{$identifier}\" is not registered");
	}

	/**
	 * @return array<string, ExpressionOptimizer>
	 */
	public function getRegistered() : array{
		return $this->registered;
	}
}