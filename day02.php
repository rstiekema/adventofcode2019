<?php

require __DIR__.'/bootstrap.php';


$input = explode(',', file_get_contents('resources/day02.txt'));

echo "Result with noun 12, verb 2: ".getIntComputerResult($input, 12, 2)."\n";

for ($noun = 0; $noun <= 99; $noun++) {
	for ($verb = 0; $verb <= 99; $verb++) {
		if (getIntComputerResult($input, $noun, $verb) == 19690720) {
			echo "Noun / verb for result 19690720: $noun / $verb\n";
			echo "100 * noun + verb: ".(100 * $noun + $verb)."\n";
			break 2;
		}
	}
}


function getIntComputerResult($input, $noun, $verb) {
	$memory = new \IntComputer\Memory($input);
	
	$memory->write(1, $noun);
	$memory->write(2, $verb);
	
	$computer = new \IntComputer\IntComputer($memory);
	$computer->run();
	
	return $memory->read(0);
}

