<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\pattern;

use Generator;
use libs\muqsit\arithmexp\expression\token\ExpressionToken;
use libs\muqsit\arithmexp\pattern\matcher\InstanceOfPatternMatcher;
use libs\muqsit\arithmexp\pattern\matcher\NotPatternMatcher;
use libs\muqsit\arithmexp\pattern\matcher\PatternMatcher;
use libs\muqsit\arithmexp\Util;

final class Pattern{

	/**
	 * @param PatternMatcher $matcher
	 * @param list<ExpressionToken|list<ExpressionToken>> $tree
	 * @return Generator<list<ExpressionToken>>
	 */
	public static function &findMatching(PatternMatcher $matcher, array &$tree) : Generator{
		/** @var list<ExpressionToken> $entry */
		foreach(Util::traverseNestedArray($tree) as &$entry){
			if($matcher->matches($entry)){
				yield $entry;
			}
		}
	}

	public static function not(PatternMatcher $matcher) : PatternMatcher{
		return new NotPatternMatcher($matcher);
	}

	/**
	 * @param class-string<ExpressionToken> $type
	 * @return PatternMatcher
	 */
	public static function instanceof(string $type) : PatternMatcher{
		return new InstanceOfPatternMatcher($type);
	}
}