<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\token;

use libs\muqsit\arithmexp\expression\token\NumericLiteralExpressionToken;
use libs\muqsit\arithmexp\Position;
use libs\muqsit\arithmexp\token\builder\ExpressionTokenBuilderState;

final class NumericLiteralToken extends SimpleToken{

	public function __construct(
		Position $position,
		readonly public int|float $value
	){
		parent::__construct(TokenType::NUMERIC_LITERAL(), $position);
	}

	public function repositioned(Position $position) : self{
		return new self($position, $this->value);
	}

	public function writeExpressionTokens(ExpressionTokenBuilderState $state) : void{
		$state->current_group[$state->current_index] = new NumericLiteralExpressionToken($this->position, $this->value);
	}

	public function __debugInfo() : array{
		$info = parent::__debugInfo();
		$info["value"] = $this->value;
		return $info;
	}

	public function jsonSerialize() : string{
		return (string) $this->value;
	}
}