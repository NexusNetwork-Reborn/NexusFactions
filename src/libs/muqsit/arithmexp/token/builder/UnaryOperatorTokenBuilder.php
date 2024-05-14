<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\token\builder;

use Generator;
use libs\muqsit\arithmexp\operator\unary\UnaryOperatorRegistry;
use libs\muqsit\arithmexp\Position;
use libs\muqsit\arithmexp\token\IdentifierToken;
use libs\muqsit\arithmexp\token\NumericLiteralToken;
use libs\muqsit\arithmexp\token\ParenthesisToken;
use libs\muqsit\arithmexp\token\UnaryOperatorToken;
use function array_keys;
use function strlen;
use function usort;

final class UnaryOperatorTokenBuilder implements TokenBuilder{

	public static function createDefault(UnaryOperatorRegistry $unary_operator_registry) : self{
		$instance = new self([]);
		$change_listener = static function(UnaryOperatorRegistry $registry) use($instance) : void{
			$operators = array_keys($registry->getRegistered());
			usort($operators, static fn(string $a, string $b) : int => strlen($b) <=> strlen($a));
			$instance->operators = $operators;
		};
		$change_listener($unary_operator_registry);
		$unary_operator_registry->registerChangeListener($change_listener);
		return $instance;
	}

	/**
	 * @param list<string> $operators
	 */
	public function __construct(
		private array $operators
	){}

	public function build(TokenBuilderState $state) : Generator{
		$token = $state->getLastCapturedToken();
		if(
			!($token instanceof NumericLiteralToken) &&
			!($token instanceof IdentifierToken) &&
			(!($token instanceof ParenthesisToken) || $token->parenthesis_mark === ParenthesisToken::MARK_OPENING)
		){
			$offset = $state->offset;
			$expression = $state->expression;
			foreach($this->operators as $operator){
				if(substr($expression, $offset, strlen($operator)) === $operator){
					yield new UnaryOperatorToken(new Position($offset, $offset + strlen($operator)), $operator);
					break;
				}
			}
		}
	}

	public function transform(TokenBuilderState $state) : void{
	}
}