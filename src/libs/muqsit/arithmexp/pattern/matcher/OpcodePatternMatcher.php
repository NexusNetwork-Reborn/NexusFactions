<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\pattern\matcher;

use libs\muqsit\arithmexp\expression\token\ExpressionToken;
use libs\muqsit\arithmexp\expression\token\OpcodeExpressionToken;
use libs\muqsit\arithmexp\token\OpcodeToken;
use function array_fill_keys;

final class OpcodePatternMatcher implements PatternMatcher{

	/**
	 * @param list<OpcodeToken::OP_*> $codes
	 * @return self
	 */
	public static function setOf(array $codes) : self{
		return new self(array_fill_keys($codes, true));
	}

	/**
	 * @param array<OpcodeToken::OP_*, true> $entries
	 */
	private function __construct(
		readonly private array $entries
	){}

	public function matches(ExpressionToken|array $entry) : bool{
		return $entry instanceof OpcodeExpressionToken && isset($this->entries[$entry->code]);
	}
}