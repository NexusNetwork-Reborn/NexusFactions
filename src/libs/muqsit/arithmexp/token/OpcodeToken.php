<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\token;

use libs\muqsit\arithmexp\expression\token\OpcodeExpressionToken;
use libs\muqsit\arithmexp\Position;
use libs\muqsit\arithmexp\token\builder\ExpressionTokenBuilderState;

final class OpcodeToken extends SimpleToken{

	public const OP_BINARY_ADD = 0;
	public const OP_BINARY_DIV = 1;
	public const OP_BINARY_EXP = 2;
	public const OP_BINARY_MOD = 3;
	public const OP_BINARY_MUL = 4;
	public const OP_BINARY_SUB = 5;
	public const OP_UNARY_NVE = 6;
	public const OP_UNARY_PVE = 7;

	/**
	 * @param self::OP_* $code
	 * @return string
	 */
	public static function opcodeToString(int $code) : string{
		return match($code){
			self::OP_BINARY_ADD, self::OP_UNARY_PVE => "+",
			self::OP_BINARY_DIV => "/",
			self::OP_BINARY_EXP => "**",
			self::OP_BINARY_MOD => "%",
			self::OP_BINARY_MUL => "*",
			self::OP_BINARY_SUB, self::OP_UNARY_NVE => "-"
		};
	}

	/**
	 * @param Position $position
	 * @param self::OP_* $code
	 * @param Token|null $parent
	 */
	public function __construct(
		Position $position,
		readonly public int $code,
		readonly public ?Token $parent = null
	){
		parent::__construct(TokenType::OPCODE(), $position);
	}

	public function repositioned(Position $position) : self{
		return new self($position, $this->code, $this->parent);
	}

	public function writeExpressionTokens(ExpressionTokenBuilderState $state) : void{
		$state->current_group[$state->current_index] = new OpcodeExpressionToken($this->position, $this->code, $this->parent);
	}

	public function __debugInfo() : array{
		$info = parent::__debugInfo();
		$info["code"] = $this->code;
		return $info;
	}

	public function jsonSerialize() : string{
		return self::opcodeToString($this->code);
	}
}