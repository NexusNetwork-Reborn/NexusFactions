<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\expression\optimizer;

use libs\muqsit\arithmexp\expression\Expression;
use libs\muqsit\arithmexp\ParseException;
use libs\muqsit\arithmexp\Parser;

interface ExpressionOptimizer{

	/**
	 * @param Parser $parser
	 * @param Expression $expression
	 * @return Expression
	 * @throws ParseException
	 */
	public function run(Parser $parser, Expression $expression) : Expression;
}