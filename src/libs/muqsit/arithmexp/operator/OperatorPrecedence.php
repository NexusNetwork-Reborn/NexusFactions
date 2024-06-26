<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\operator;

interface OperatorPrecedence{

	public const EXPONENTIAL = 0;
	public const UNARY_NEGATIVE_POSITIVE = 1;
	public const MULTIPLICATION_DIVISION_MODULO = 2;
	public const ADDITION_SUBTRACTION = 3;
}