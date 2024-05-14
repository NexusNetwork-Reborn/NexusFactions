<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\constant;

use libs\muqsit\arithmexp\Parser;
use libs\muqsit\arithmexp\token\builder\ExpressionTokenBuilderState;
use libs\muqsit\arithmexp\token\IdentifierToken;

interface ConstantInfo{

	public function writeExpressionTokens(Parser $parser, string $expression, IdentifierToken $token, ExpressionTokenBuilderState $state) : void;
}