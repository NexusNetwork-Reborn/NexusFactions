<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\operator\assignment;

use Generator;
use libs\muqsit\arithmexp\operator\OperatorList;
use libs\muqsit\arithmexp\token\UnaryOperatorToken;
use function count;

final class NullOperatorAssignment implements OperatorAssignment{

	public static function instance() : self{
		static $instance = null;
		return $instance ??= new self();
	}

	private function __construct(){
	}

	public function getType() : int{
		return self::TYPE_NA;
	}

	public function traverse(OperatorList $list, array &$tokens) : Generator{
		$state = new OperatorAssignmentTraverserState($tokens);
		$operators = $list->unary;
		for($i = count($tokens) - 1; $i >= 0; --$i){
			$token = $tokens[$i];
			if($token instanceof UnaryOperatorToken && isset($operators[$token->operator])){
				$state->index = $i;
				$state->value = $token;
				yield $state;
				if($state->changed){
					$i = count($tokens) - 1;
					$state->changed = false;
				}
			}
		}
	}
}