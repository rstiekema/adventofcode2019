<?php

require __DIR__.'/bootstrap.php';


$input    = "1,1,1,4,99,5,6,0,99";
$expected = "30,1,1,4,2,5,6,0,99";

$input    = "1002,4,3,4,33";
$expected = "1002,4,3,4,99";

$input    = "1101,100,-1,4,0";
$expected = "1101,100,-1,4,99";

$input    = "3,9,8,9,10,9,4,9,99,-1,8"; // position mode, equal to 8 = output 1
$input    = "3,9,7,9,10,9,4,9,99,-1,8"; // position mode, less than 8 = output 1
$input    = "3,3,1108,-1,8,3,4,3,99"; // immediate mode, equal to 8 = output 1
$input    = "3,3,1107,-1,8,3,4,3,99"; // immediate mode, less than 8 = output 1
$input    = "3,12,6,12,15,1,13,14,13,4,13,99,-1,0,1,9"; // position mode, jump test, output 0 if input 0, else output 1
$input    = "3,3,1105,-1,9,1101,0,0,12,4,12,99,1"; // immediate mode, jump test, output 0 if input 0, else output 1
$input    = "3,21,1008,21,8,20,1005,20,22,107,8,21,20,1006,20,31,1106,0,36,98,0,0,1002,21,125,20,4,20,1105,1,46,104,999,1105,1,46,1101,1000,1,20,4,20,1105,1,46,98,99"; // output 999 if input below 8, output 1000 if input equal to 8, output 1001 if input greater than 8


$input = file_get_contents('resources/day05.txt');


$memory = new \IntcodeComputer\Memory(explode(",", $input));
$computer = new \IntcodeComputer\IntcodeComputer($memory);
$computer->run();


echo "Program output: ".$computer->getOutput()."\n";


