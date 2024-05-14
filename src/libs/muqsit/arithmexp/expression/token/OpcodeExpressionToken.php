<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\expression\token;

use libs\muqsit\arithmexp\Position;
use libs\muqsit\arithmexp\token\OpcodeToken;
use libs\muqsit\arithmexp\token\Token;

final class OpcodeExpressionToken implements ExpressionToken{

	/**
	 * @param Position $position
	 * @param OpcodeToken::OP_* $code
	 * @param Token|null $parent
	 */
	public function __construct(
		public Position $position,
		public int $code,
		public ?Token $parent = null
	){}

	public function getPos() : Position{
		return $this->position;
	}

	public function isDeterministic() : bool{
		return true;
	}

	public function equals(ExpressionToken $other) : bool{
		return $other instanceof self && $other->code === $this->code;
	}

	public function __toString() : string{
		return OpcodeToken::opcodeToString($this->code);
	}
}