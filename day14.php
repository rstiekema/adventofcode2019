<?php

$input   = file_get_contents('resources/day14.txt');
$factory = new NanoFactory($input);


// Part 1
$factory->loadChemicalsForFuel(1);
$requiredORE = $factory->getRequiredORE();
echo "Total ORE required: $requiredORE\n";


// Part 2
$totalOre     = 1000000000000;
$minGuess     = $requiredORE;
$maxGuess     = $requiredORE * 100;
$currentGuess = $maxGuess;
$guessedFuels = [];

while (true) {
	$currentGuess = $minGuess + floor(($maxGuess - $minGuess) / 2);
	
	$factory->loadChemicalsForFuel($currentGuess);
	$requiredORE = $factory->getRequiredORE();
	
	if ($requiredORE == $totalOre || in_array($currentGuess, $guessedFuels)) {
		break;
		
	} else if ($requiredORE < $totalOre) {
		$minGuess = $currentGuess;
		$guessedFuels[] = $currentGuess;
		
	} else {
		$maxGuess = $currentGuess;
	}
}

echo "maximum FUEL for $totalOre ORE: $currentGuess\n";





class NanoFactory
{
	
	private $reactions = [];
	private $requiredChemicals = [];
	private $reserveChemicals = [];
	
	
	public function __construct($input)
	{
		foreach (explode("\n", $input) as $reactionData) {
			list($inputData, $outputData) = explode(' => ', $reactionData);
			list($outputIncrementedBy, $outputType) = explode(' ', $outputData);
			
			$reaction = [
				'amount' => $outputIncrementedBy,
				'input'  => []
			];
		
			foreach (explode(', ', $inputData) as $inputChemical) {
				list($inputAmount, $inputType) = explode(' ', $inputChemical);
				$reaction['input'][$inputType] = $inputAmount;
			}
			
			$this->reactions[$outputType] = $reaction;
		}
	}
	
	
	private function calculateRequiredChemicals($chemical = 'FUEL', $requiredAmount = 1): void
	{
		$reaction       = $this->reactions[$chemical];
		$inputs         = $reaction['input'];
		$producedAmount = $reaction['amount'];
		
		if (isset($this->reserveChemicals[$chemical])) {
			$useReserve = min($this->reserveChemicals[$chemical], $requiredAmount);
	
			if ($useReserve > 0) {
				$requiredAmount -= $useReserve;
				$this->reserveChemicals[$chemical] -= $useReserve;
			}
		}
		
		if ($requiredAmount == 0) {
			return;
		}
		
		$multiplier          = ceil($requiredAmount / $producedAmount);
		$totalProducedAmount = $multiplier * $producedAmount;
		$wasted              = $totalProducedAmount - $requiredAmount;
		
		if (!isset($this->requiredChemicals[$chemical])) {
			$this->requiredChemicals[$chemical] = 0;
		}
		
		if (!isset($this->reserveChemicals[$chemical])) {
			$this->reserveChemicals[$chemical] = 0;
		}
		
		$this->requiredChemicals[$chemical] += $totalProducedAmount;
		$this->reserveChemicals[$chemical] += $wasted;
		
		foreach ($inputs as $inputType => $inputAmount) {
			if ($inputType == 'ORE') {
				continue;
			}
			
			$this->calculateRequiredChemicals($inputType, $inputAmount * $multiplier);
		}
	}
	
	
	public function loadChemicalsForFuel(int $fuel)
	{
		$this->requiredChemicals = [];
		$this->reserveChemicals = [];
		$this->calculateRequiredChemicals('FUEL', $fuel);
	}
	
	
	public function getRequiredORE(): int
	{
		$total = 0;
		
		foreach ($this->requiredChemicals as $chemicalType => $requiredAmount) {
			if (isset($this->reactions[$chemicalType]['input']['ORE'])) {
				$oreAmount      = $this->reactions[$chemicalType]['input']['ORE'];
				$producedAmount = $this->reactions[$chemicalType]['amount'];
				$multiplier     = ceil($requiredAmount / $producedAmount);
				
				$total += $oreAmount * $multiplier;
			}
		}
		
		return $total;
	}
}



