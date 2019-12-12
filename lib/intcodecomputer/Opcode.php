<?php

namespace IntcodeComputer;

class Opcode
{
	const OPCODE_ADD                = 1;
	const OPCODE_MULTIPLY           = 2;
	const OPCODE_INPUT              = 3;
	const OPCODE_OUTPUT             = 4;
	const OPCODE_JUMPIFTRUE         = 5;
	const OPCODE_JUMPIFFALSE        = 6;
	const OPCODE_LESSTHAN           = 7;
	const OPCODE_EQUALS             = 8;
	const OPCODE_ADJUSTRELATIVEBASE = 9;
	const OPCODE_EXIT               = 99;
	
	
	public static function getOpcode(int $firstInstructionValue)
	{
		return (int)substr($firstInstructionValue, -2);
	}
	
}
