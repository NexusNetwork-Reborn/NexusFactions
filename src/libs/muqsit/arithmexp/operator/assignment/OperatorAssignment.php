<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\operator\assignment;

use Generator;
use libs\muqsit\arithmexp\operator\OperatorList;
use libs\muqsit\arithmexp\token\Token;

interface OperatorAssignment{

	public const TYPE_LEFT = 0;
	public const TYPE_RIGHT = 1;
	public const TYPE_NA = 2;

	/**
	 * @return self::TYPE_*
	 */
	public function getType() : int;

	/**
	 * @param OperatorList $list
	 * @param list<Token|list<Token>> $tokens
	 * @return Generator<OperatorAssignmentTraverserState>
	 */
	public function traverse(OperatorList $list, array &$tokens) : Generator;
}