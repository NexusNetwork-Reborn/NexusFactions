<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\token\builder;

use Generator;
use libs\muqsit\arithmexp\Position;
use libs\muqsit\arithmexp\token\ParenthesisToken;

final class ParenthesisTokenBuilder implements TokenBuilder{

	/** @var array<string, array{ParenthesisToken::MARK_*, ParenthesisToken::TYPE_*}> */
	private array $symbols_to_mark_type = [];

	public function __construct(){
		foreach([ParenthesisToken::MARK_OPENING, ParenthesisToken::MARK_CLOSING] as $mark){
			foreach([ParenthesisToken::TYPE_ROUND, ParenthesisToken::TYPE_SQUARE, ParenthesisToken::TYPE_CURLY] as $type){
				$this->symbols_to_mark_type[ParenthesisToken::symbolFrom($mark, $type)] = [$mark, $type];
			}
		}
	}

	public function build(TokenBuilderState $state) : Generator{
		$char = $state->expression[$state->offset];
		if(isset($this->symbols_to_mark_type[$char])){
			[$mark, $type] = $this->symbols_to_mark_type[$char];
			yield new ParenthesisToken(new Position($state->offset, $state->offset + 1), $mark, $type);
		}
	}

	public function transform(TokenBuilderState $state) : void{
	}
}