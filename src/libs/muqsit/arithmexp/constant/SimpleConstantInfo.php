<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\constant;

use libs\muqsit\arithmexp\expression\token\NumericLiteralExpressionToken;
use libs\muqsit\arithmexp\Parser;
use libs\muqsit\arithmexp\token\builder\ExpressionTokenBuilderState;
use libs\muqsit\arithmexp\token\IdentifierToken;

final class SimpleConstantInfo implements ConstantInfo{

	public function __construct(
		readonly public int|float $value
	){}

	public function writeExpressionTokens(Parser $parser, string $expression, IdentifierToken $token, ExpressionTokenBuilderState $state) : void{
		$state->current_group[$state->current_index] = new NumericLiteralExpressionToken($token->getPos(), $this->value);
	}
}