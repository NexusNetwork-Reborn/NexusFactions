<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\operator\assignment;

use libs\muqsit\arithmexp\token\BinaryOperatorToken;
use libs\muqsit\arithmexp\token\Token;
use libs\muqsit\arithmexp\token\UnaryOperatorToken;

final class OperatorAssignmentTraverserState{

	public int $index;
	public BinaryOperatorToken|UnaryOperatorToken $value;
	public bool $changed = false;

	/**
	 * @param list<Token> $tokens
	 */
	public function __construct(
		private array &$tokens
	){}

	/**
	 * @param int $offset
	 * @param int $length
	 * @param list<Token|list<Token>> $replacement
	 */
	public function splice(int $offset, int $length, array $replacement) : void{
		array_splice($this->tokens, $offset, $length, $replacement);
		$this->changed = true;
	}
}