<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\operator\unary;

use libs\muqsit\arithmexp\function\FunctionInfo;

interface UnaryOperator{

	public function getSymbol() : string;

	public function getPrecedence() : int;

	public function getName() : string;

	public function getFunction() : FunctionInfo;
}