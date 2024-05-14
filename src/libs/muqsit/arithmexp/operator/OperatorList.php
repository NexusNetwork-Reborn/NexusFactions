<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\operator;

use libs\muqsit\arithmexp\operator\assignment\OperatorAssignment;
use libs\muqsit\arithmexp\operator\binary\BinaryOperator;
use libs\muqsit\arithmexp\operator\unary\UnaryOperator;

final class OperatorList{

	/**
	 * @param OperatorAssignment $assignment
	 * @param array<string, BinaryOperator> $binary
	 * @param array<string, UnaryOperator> $unary
	 */
	public function __construct(
		readonly public OperatorAssignment $assignment,
		readonly public array $binary,
		readonly public array $unary
	){}
}