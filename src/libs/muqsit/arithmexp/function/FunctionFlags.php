<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\function;

interface FunctionFlags{

	public const COMMUTATIVE = 1 << 0;
	public const DETERMINISTIC = 1 << 1;
	public const IDEMPOTENT = 1 << 2;
}