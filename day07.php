<?php

require __DIR__.'/bootstrap.php';


$program = explode(',', file_get_contents('resources/day07.txt'));

// Max thruster signal 43210 (from phase setting sequence 4,3,2,1,0)
//$program = explode(',', '3,15,3,16,1002,16,10,16,1,16,15,15,4,15,99,0,0');

// Max thruster signal 54321 (from phase setting sequence 0,1,2,3,4):
//$program = explode(',', '3,23,3,24,1002,24,10,24,1002,23,-1,23,101,5,23,23,1,24,23,23,4,23,99,0,0');

// Max thruster signal 65210 (from phase setting sequence 1,0,4,3,2):
//$program = explode(',', '3,31,3,32,1002,32,10,32,1001,31,-2,31,1007,31,0,33,1002,33,7,33,1,33,31,31,1,32,31,31,4,31,99,0,0,0');


$maxSignal = 0;

foreach (getPermutations(range(0, 4)) as $phaseSettings) {
	$input = 0;
	
	foreach ($phaseSettings as $phaseSetting) {
		$memory    = new \IntcodeComputer\Memory($program);
		$amplifier = new \IntcodeComputer\IntcodeComputer($memory);
		
		$amplifier->addInput($phaseSetting);
		$amplifier->addInput($input);
		$amplifier->run();
		
		$output = $amplifier->getOutput();
		$input  = $output; // Set for the next amplifier
	}
	
	if ($output > $maxSignal) {
		$maxSignal = $output;
	}
}

echo "Max signal: $maxSignal\n";




// Max thruster signal 139629729 (from phase setting sequence 9,8,7,6,5)
//$program = explode(',', '3,26,1001,26,-4,26,3,27,1002,27,2,27,1,27,26,27,4,27,1001,28,-1,28,1005,28,6,99,0,0,5');
//$permutations = [[9,8,7,6,5]];

// Max thruster signal 18216 (from phase setting sequence 9,7,8,5,6)
//$program = explode(',', '3,52,1001,52,-5,52,3,53,1,52,56,54,1007,54,5,55,1005,55,26,1001,54,-5,54,1105,1,12,1,53,54,53,1008,54,0,55,1001,55,1,55,2,53,55,53,4,53,1001,56,-1,56,1005,56,6,99,0,0,0,0,10');
//$permutations = [[9,7,8,5,6]];


$maxSignal    = 0;
$permutations = getPermutations(range(5, 9));


foreach ($permutations as $phaseSettings) {
	$amplifiers = [];
	
	
	// Initialize the amplifiers
	for ($i = 0; $i < count($phaseSettings); $i++) {
		$phaseSetting = $phaseSettings[$i];
		
		$memory    = new \IntcodeComputer\Memory($program);
		$amplifier = new \IntcodeComputer\IntcodeComputer($memory);
		
		$amplifier->addInput($phaseSetting);
		$amplifiers[$i] = $amplifier;
	}
	
	
	// Connect the outputs / inputs
	foreach ($amplifiers as $computerKey => $amplifier) {
		$nextAmplifier = isset($amplifiers[$computerKey + 1]) ? $amplifiers[$computerKey + 1] : $amplifiers[0];
		$amplifier->setOutputCallback(function($output) use ($amplifier, $nextAmplifier) {
			$amplifier->pause();
			$nextAmplifier->addInput($output);

			if (!$nextAmplifier->started) {
				$nextAmplifier->run();
			}
		});
	}
	
	
	// Start the first amplifier
	$amplifiers[0]->addInput(0);
	$amplifiers[0]->run();
	
	
	// Get the output from the last amplifier
	$output = $amplifiers[4]->getOutput();
	
	if ($output > $maxSignal) {
		$maxSignal = $output;
	}
}


echo "Max signal in loopback mode: $maxSignal\n";




function getPermutations(array $elements) {
    if (count($elements) <= 1) {
        yield $elements;
        
    } else {
        foreach (getPermutations(array_slice($elements, 1)) as $permutation) {
            foreach (range(0, count($elements) - 1) as $i) {
                yield array_merge(
                    array_slice($permutation, 0, $i),
                    [$elements[0]],
                    array_slice($permutation, $i)
                );
            }
        }
    }
}
