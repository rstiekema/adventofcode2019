<?php

namespace IntcodeComputer;

class Parameter
{
	const MODE_LOCATION  = 0;
	const MODE_IMMEDIATE = 1;
	const MODE_RELATIVE  = 2;
	
	
	public static function getMode(int $firstInstructionValue, int $parameter)
	{
		if (strlen($firstInstructionValue) < 2 + $parameter) {
			return self::MODE_LOCATION;
		}
		
		return (int)substr($firstInstructionValue, -2 - $parameter, 1);
	}
	
}
