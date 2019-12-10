<?php

list($from, $to) = explode('-', file_get_contents('resources/day04.txt'));

$total1 = 0;
$total2 = 0;

for ($i = $from; $i <= $to; $i++) {
	if (meetsRequirements($i)) {
		$total1++;
	}
	
	if (meetsRequirements($i, true)) {
		$total2++;
	}
}

echo "$total1 passwords in range $from to $to\n";
echo "$total2 passwords in range $from to $to (extended validation)\n";


function meetsRequirements($password, $extendedValidation = false) {
	// Two adjacent digits are the same (like 22 in 122345).
	// 123789 does not meet these criteria (no double).
	$sameDigitsFound = [];
	
	for ($i = 0; $i <= 9; $i++) {
		if (strpos($password, str_repeat($i, 2)) !== false) {
			$sameDigitsFound[] = $i;
		}
	}
	
	if (empty($sameDigitsFound)) {
		return false;
	}
	
	
	// Going from left to right, the digits never decrease; they only ever increase or stay the same (like 111123 or 135679).
	// 111111 meets these criteria (double 11, never decreases).
	// 223450 does not meet these criteria (decreasing pair of digits 50).
	$prevChar = 0;
	
	foreach (str_split($password) as $char) {
		if ($char < $prevChar) {
			return false;
		}
		
		$prevChar = $char;
	}
	
	
	// Extended validation:
	// The two adjacent matching digits are not part of a larger group of matching digits.
	// 112233 meets these criteria because the digits never decrease and all repeated digits are exactly two digits long.
	// 123444 no longer meets the criteria (the repeated 44 is part of a larger group of 444).
	// 111122 meets the criteria (even though 1 is repeated more than twice, it still contains a double 22).
	if ($extendedValidation) {
		$foundDouble = false;
		
		foreach ($sameDigitsFound as $digit) {
			if (strpos($password, str_repeat($digit, 3)) === false) {
				$foundDouble = true;
			}
		}
		
		if (!$foundDouble) {
			return false;
		}
	}
	
	return true;
}