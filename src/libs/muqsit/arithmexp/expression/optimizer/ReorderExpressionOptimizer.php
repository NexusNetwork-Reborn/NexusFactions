<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\expression\optimizer;

use libs\muqsit\arithmexp\expression\Expression;
use libs\muqsit\arithmexp\expression\RawExpression;
use libs\muqsit\arithmexp\expression\token\ExpressionToken;
use libs\muqsit\arithmexp\expression\token\FunctionCallExpressionToken;
use libs\muqsit\arithmexp\expression\token\NumericLiteralExpressionToken;
use libs\muqsit\arithmexp\expression\token\VariableExpressionToken;
use libs\muqsit\arithmexp\function\FunctionFlags;
use libs\muqsit\arithmexp\Parser;
use libs\muqsit\arithmexp\Util;
use function array_key_last;
use function array_values;
use function count;
use function is_array;
use function usort;

final class ReorderExpressionOptimizer implements ExpressionOptimizer{

	public function __construct(){
	}

	public function run(Parser $parser, Expression $expression) : Expression{
		$postfix_expression_tokens = $expression->getPostfixExpressionTokens();
		$tree = Util::expressionTokenArrayToTree($parser, $postfix_expression_tokens);
		$changed = false;
		foreach(Util::traverseNestedArray($tree) as &$entry){
			$index = array_key_last($entry);
			if($index === null || !($entry[$index] instanceof ExpressionToken)){
				continue;
			}

			$token = Util::asFunctionCallExpressionToken($parser, $entry[$index]);
			if($token === null){
				continue;
			}

			if(($token->flags & FunctionFlags::COMMUTATIVE) === 0){
				continue;
			}

			$updated = array_values($entry); // copy &array
			Util::flattenArray($updated, static fn(array $e) : bool => count($e) > 2 &&
				($index = array_key_last($e)) !== null &&
				$e[$index] instanceof ExpressionToken &&
				($other_token = Util::asFunctionCallExpressionToken($parser, $e[$index])) !== null &&
				($other_token->flags & FunctionFlags::COMMUTATIVE) > 0 &&
				$other_token->function === $token->function
			);
			usort($updated, fn(ExpressionToken|array $a, ExpressionToken|array $b) : int => match(true){
				is_array($a) => is_array($b) ? 0 : -1,
				is_array($b) => 1,
				default => $this->compare(Util::asFunctionCallExpressionToken($parser, $a) ?? $a, Util::asFunctionCallExpressionToken($parser, $b) ?? $b)
			});
			if($updated !== $entry){
				$entry = $updated;
				$changed = true;
			}
		}

		unset($entry);
		if(!$changed){
			return $expression;
		}

		Util::flattenArray($tree);
		return new RawExpression($expression->getExpression(), $tree);
	}

	/**
	 * @param ExpressionToken $a
	 * @param ExpressionToken $b
	 * @return int<-1, 1>
	 */
	private function compare(ExpressionToken $a, ExpressionToken $b) : int{
		if($a instanceof FunctionCallExpressionToken && $b instanceof FunctionCallExpressionToken){
			return 0;
		}
		if($a instanceof FunctionCallExpressionToken){
			return 1;
		}
		if($b instanceof FunctionCallExpressionToken){
			return -1;
		}

		if($a instanceof VariableExpressionToken && $b instanceof VariableExpressionToken){
			return $a->label <=> $b->label;
		}
		if($a instanceof VariableExpressionToken){
			return -1;
		}
		if($b instanceof VariableExpressionToken){
			return 1;
		}

		if($a instanceof NumericLiteralExpressionToken && $b instanceof NumericLiteralExpressionToken){
			return $a->value <=> $b->value;
		}
		return 0;
	}
}