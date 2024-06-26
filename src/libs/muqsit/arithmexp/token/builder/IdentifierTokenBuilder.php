<?php

declare(strict_types=1);

namespace libs\muqsit\arithmexp\token\builder;

use Generator;
use libs\muqsit\arithmexp\Position;
use libs\muqsit\arithmexp\token\IdentifierToken;

final class IdentifierTokenBuilder implements TokenBuilder{

	public function __construct(){
	}

	public function build(TokenBuilderState $state) : Generator{
		$name = "";
		$offset = $state->offset;
		$start = $offset;
		$length = $state->length;
		$expression = $state->expression;

		while($offset < $length){
			$char = $expression[$offset];
			if($char !== "_" && ($offset === $start ? !ctype_alpha($char) : !ctype_alnum($char))){
				break;
			}

			$name .= $char;
			$offset++;
		}

		if($name !== ""){
			yield new IdentifierToken(new Position($start, $offset), $name);
		}
	}

	public function transform(TokenBuilderState $state) : void{
	}
}