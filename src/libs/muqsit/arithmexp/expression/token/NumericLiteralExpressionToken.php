<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\expression\token;

use libs\muqsit\arithmexp\Position;

final class NumericLiteralExpressionToken implements ExpressionToken{

	public function __construct(
		public Position $position,
		public int|float $value
	){}

	public function getPos() : Position{
		return $this->position;
	}

	public function isDeterministic() : bool{
		return true;
	}

	public function equals(ExpressionToken $other) : bool{
		return $other instanceof self && $other->value === $this->value;
	}

	public function __toString() : string{
		return (string) $this->value;
	}
}