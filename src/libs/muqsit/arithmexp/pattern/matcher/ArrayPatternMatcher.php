<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\pattern\matcher;

use libs\muqsit\arithmexp\expression\token\ExpressionToken;
use function count;
use function is_array;

final class ArrayPatternMatcher implements PatternMatcher{

	/**
	 * @param list<PatternMatcher> $patterns
	 */
	public function __construct(
		readonly private array $patterns
	){}

	public function matches(ExpressionToken|array $entry) : bool{
		$patterns_c = count($this->patterns);
		if(!is_array($entry) || count($entry) !== $patterns_c){
			return false;
		}
		for($i = 0; $i < $patterns_c; ++$i){
			if(!$this->patterns[$i]->matches($entry[$i])){
				return false;
			}
		}
		return true;
	}
}