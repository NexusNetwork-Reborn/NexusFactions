<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\pattern\matcher;

use libs\muqsit\arithmexp\expression\token\ExpressionToken;

final class NotPatternMatcher implements PatternMatcher{

	public function __construct(
		readonly private PatternMatcher $matcher
	){}

	public function matches(ExpressionToken|array $entry) : bool{
		return !$this->matcher->matches($entry);
	}
}