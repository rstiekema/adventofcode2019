<?php


$modules = file('resources/day01.txt', FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

echo "Total fuel required: ".calculateTotalFuel($modules)."\n";
echo "Total fuel required (including fuel mass): ".calculateTotalFuel($modules, true)."\n";



function calculateTotalFuel($modules, $includeFuelMass = false) {
	$total = 0;

	foreach ($modules as $mass) {
		$total += calculateFuel($mass, $includeFuelMass);
	}
	
	return $total;
}

function calculateFuel($mass, $includeFuelMass) {
	$fuel = floor($mass / 3) - 2;
	
	if ($fuel <= 0) {
		return 0;
	}
	
	if ($includeFuelMass) {
		$fuel += calculateFuel($fuel, true);
	}
	
	return $fuel;
}