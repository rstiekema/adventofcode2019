<?php


echo "Result with noun 12, verb 2: ".getIntComputerResult(12, 2)."\n";


for ($noun = 0; $noun <= 99; $noun++) {
	for ($verb = 0; $verb <= 99; $verb++) {
		if (getIntComputerResult($noun, $verb) == 19690720) {
			echo "Noun / verb for result 19690720: $noun / $verb\n";
			echo "100 * noun + verb: ".(100 * $noun + $verb)."\n";
			break 2;
		}
	}
}


function getIntComputerResult($noun, $verb) {
	$list = explode(',', file_get_contents('resources/day02.txt'));
	$list[1] = $noun;
	$list[2] = $verb;
	
	$instructionLength = 4;
	
	for ($instructionPointer = 0; $instructionPointer < count($list); $instructionPointer += $instructionLength) {
		$instruction   = array_slice($list, $instructionPointer, $instructionLength);
		$opcode        = $instruction[0];
		$value1Address = $instruction[1];
		$value2Address = $instruction[2];
		$value3Address = $instruction[3];
		
		switch ($opcode) {
			case 1:
				$list[$value3Address] = $list[$value1Address] + $list[$value2Address];
				break;
				
			case 2:
				$list[$value3Address] = $list[$value1Address] * $list[$value2Address];
				break;
				
			case 99:
				break 2;
		}
	}
	
	return $list[0];
}

