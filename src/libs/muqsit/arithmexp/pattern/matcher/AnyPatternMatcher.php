<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\pattern\matcher;

use libs\muqsit\arithmexp\expression\token\ExpressionToken;

final class AnyPatternMatcher implements PatternMatcher{

	public static function instance() : self{
		static $instance = null;
		return $instance ??= new self();
	}

	private function __construct(){
	}

	public function matches(ExpressionToken|array $entry) : bool{
		return true;
	}
}