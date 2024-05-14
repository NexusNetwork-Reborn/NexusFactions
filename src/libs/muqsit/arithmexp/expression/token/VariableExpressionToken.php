<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\expression\token;

use libs\muqsit\arithmexp\Position;

final class VariableExpressionToken implements ExpressionToken{

	public function __construct(
		public Position $position,
		public string $label
	){}

	public function getPos() : Position{
		return $this->position;
	}

	public function isDeterministic() : bool{
		return false;
	}

	public function equals(ExpressionToken $other) : bool{
		return $other instanceof self && $other->label === $this->label;
	}

	public function __toString() : string{
		return $this->label;
	}
}