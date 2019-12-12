<?php

require __DIR__.'/bootstrap.php';


$program = explode(',', file_get_contents('resources/day09.txt'));


//$program = explode(',', '109,1,204,-1,1001,100,1,100,1008,100,16,101,1006,101,0,99'); // takes no input and produces a copy of itself as output
// 109,1				add 1 to relativebase
// 204,-1				ouput value at relativebase address + -1
// 1001,100,1,100		add value at address 100 and value 1 and store in address 100
// 1008,100,16,101		check if value at address 100 is the same as 16. store value 1 at address 101 if true, 0 if false
// 1006,101,0			jump to address 0 if value at address 101 is 0
// 99					exit

//$program = explode(',', '1102,34915192,34915192,7,4,7,99,0'); // should output a 16-digit number.
//$program = explode(',', '104,1125899906842624,99'); // should output the large number in the middle.


$memory   = new \IntcodeComputer\Memory($program);
$computer = new \IntcodeComputer\IntcodeComputer($memory);
$computer->run();

echo "Output: ".$computer->getOutput()."\n";

