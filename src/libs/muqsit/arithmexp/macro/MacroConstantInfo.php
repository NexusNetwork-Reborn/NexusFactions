<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\macro;

use Closure;
use InvalidArgumentException;
use libs\muqsit\arithmexp\constant\ConstantInfo;
use libs\muqsit\arithmexp\Parser;
use libs\muqsit\arithmexp\token\builder\ExpressionTokenBuilderState;
use libs\muqsit\arithmexp\token\IdentifierToken;
use libs\muqsit\arithmexp\token\Token;
use function array_splice;
use function count;

final class MacroConstantInfo implements ConstantInfo{

	/**
	 * @param Closure(Parser $parser, string $expression, IdentifierToken $token) : list<Token> $resolver
	 */
	public function __construct(
		readonly public Closure $resolver
	){}

	public function writeExpressionTokens(Parser $parser, string $expression, IdentifierToken $token, ExpressionTokenBuilderState $state) : void{
		$result = ($this->resolver)($parser, $expression, $token);
		if(count($result) === 0){
			throw new InvalidArgumentException("Macro must return a list of at least one element");
		}

		array_splice($state->current_group, $state->current_index, 1, $result);
		$state->current_index += count($result);
	}
}