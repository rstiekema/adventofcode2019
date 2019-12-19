<?php

$input      = file_get_contents('resources/day16.txt');
$phases     = 100;
$expected   = '';

//$input    = '12345678';
//$phases   = 4;
//$expected = '01029498';

//$input    = '80871224585914546619083218645595';
//$phases   = 100;
//$expected = '24176176';

//$input    = '19617804207202209144916044189917';
//$phases   = 100;
//$expected = '73745418';

//$input    = '69317163492948606335995924319873';
//$phases   = 100;
//$expected = '52432133';



// Part 1
//for ($i = 1; $i <= $phases; $i++) {
//	$input = getPhaseOutput($input);
//}
//
//$result = substr($input, 0, 8);
//
//echo "\n";
//echo "First 8 digits of last output: $result\n";
//if (!empty($expected)) echo $result == $expected ? "YAY!" : "BOOO!";
//echo "\n";





// Part 2
$input     = str_repeat('03036732577212944063491565474664', 10000);
$offset    = substr($input, 0, 7);
$phases    = 100;
$expected  = '84462026';
$chars     = str_split($input);

for ($i = 1; $i <= $phases; $i++) {
	$input = getPhaseOutput($chars);
	echo ".";
}

$result = substr($input, $offset, 8);

echo "\n";
echo "First 8 digits from offset $offset of last output: $result\n";
if (!empty($expected)) echo $result == $expected ? "YAY!" : "BOOO!";






function getPhaseOutput($chars) {
	$pattern  = [0, 1, 0, -1];
	$length   = count($chars);
	$result   = '';
	
	echo "getting result of $length chars...";
	
	for ($i = 0; $i < $length; $i++) {
//		$total = 0;
		
		foreach ($chars as $charKey => $char) {
			$patternPosTotal   = ($charKey + 1) % (count($pattern) * ($i + 1));
			$patternPosDefault = $patternPosTotal > 0 ? floor($patternPosTotal / ($i + 1)) : 0;
			
			$multiplier = $pattern[$patternPosDefault];
//			$total     += $char * $multiplier;
		}
		
		if ($i % 10 == 0) {
			echo "$i-";
		}
		
//		$result .= substr($total, -1);
	}
	
	echo "\n";
	
	return $result;
}


function getMultiplier(int $iterationKey, int $charKey) {
	$pattern           = [0, 1, 0, -1];
	$patternPosTotal   = ($charKey + 1) % (count($pattern) * ($iterationKey + 1));
	$patternPosDefault = $patternPosTotal > 0 ? floor($patternPosTotal / ($iterationKey + 1)) : 0;
	
	return $pattern[$patternPosDefault];
}


