<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\expression\token;

use libs\muqsit\arithmexp\Position;
use Stringable;

interface ExpressionToken extends Stringable{

	public function getPos() : Position;

	public function isDeterministic() : bool;

	public function equals(self $other) : bool;
}