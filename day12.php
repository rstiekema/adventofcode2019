<?php

$input = file_get_contents('resources/day12.txt');
$steps = 1000;


//$input = "<x=-1, y=0, z=2>
//<x=2, y=-10, z=-7>
//<x=4, y=-8, z=8>
//<x=3, y=5, z=-1>";
//$steps = 10;


//$input = "<x=-8, y=-10, z=0>
//<x=5, y=5, z=10>
//<x=2, y=-7, z=3>
//<x=9, y=-8, z=-3>";
//$steps = 100;



$simulator = new MoonMotionSimulator($input);

for ($i = 1; $i <= $steps; $i++) {
	$simulator->step();
}

echo "Total energy: ".$simulator->getEnergy()."\n";




class MoonMotionSimulator
{
	private $moons = [];
	private $moonPairs = [];
	
	
	public function __construct($moonData)
	{
		foreach (explode("\n", $moonData) as $moonCoordinateData) {
			$coordinate    = MoonCoordinate::parse($moonCoordinateData);
			$this->moons[] = new Moon($coordinate);
		}
		
		foreach ($this->moons as $firstKey => $first) {
			foreach ($this->moons as $secondKey => $second) {
				if (isset($this->moonPairs[$firstKey.'-'.$secondKey])) continue;
				if (isset($this->moonPairs[$secondKey.'-'.$firstKey])) continue;
				if ($firstKey == $secondKey) continue;
				
				$this->moonPairs[$firstKey.'-'.$secondKey] = new MoonPair($first, $second);
			}
		}
	}
	
	
	public function step(): void
	{
		/**
		 * @var MoonPair $moonPair
		 * @var Moon $moon
		 */
		
		foreach ($this->moonPairs as $moonPair) {
			$moonPair->applyGravity();
		}
		
		foreach ($this->getMoons() as $moon) {
			$moon->applyVelocity();
		}
		
	}
	
	
	public function getMoons(): array
	{
		return $this->moons;
	}
	
	
	public function getEnergy(): int
	{
		$energy = 0;
		
		foreach ($this->getMoons() as $moon) {
			$energy += $moon->getPotentialEnergy() * $moon->getKineticEnergy();
		}
		
		return $energy;
	}
	
}



class MoonPair
{
	private $first;
	private $second;
	
	
	public function __construct(Moon $first, Moon $second)
	{
		$this->first  = $first;
		$this->second = $second;
	}
	
	
	public function first(): Moon
	{
		return $this->first;
	}
	
	
	public function second(): Moon
	{
		return $this->second;
	}
	
	
	public function applyGravity(): void
	{
		foreach (['x', 'y', 'z'] as $axis) {
			$firstAxisValueX  = $this->first()->getPosition()->get($axis);
			$secondAxisValueX = $this->second()->getPosition()->get($axis);
			
			if ($firstAxisValueX == $secondAxisValueX) {
				continue;
			}
			
			$this->first()->getVelocity()->add($axis, $firstAxisValueX > $secondAxisValueX ? -1 : 1);
			$this->second()->getVelocity()->add($axis, $secondAxisValueX > $firstAxisValueX ? -1 : 1);
		}
	}
	
}



class Moon
{
	private $position;
	private $velocity;
	
	
	public function __construct(MoonCoordinate $position)
	{
		$this->position = $position;
		$this->velocity = new MoonCoordinate(0, 0, 0);
	}
	
	
	public function getPosition(): MoonCoordinate
	{
		return $this->position;
	}
	
	
	public function getVelocity(): MoonCoordinate
	{
		return $this->velocity;
	}
	
	
	public function applyVelocity(): void
	{
		foreach (['x', 'y', 'z'] as $axis) {
			$this->getPosition()->add($axis, $this->getVelocity()->get($axis));
		}
	}
	
	
	public function getPotentialEnergy(): int
	{
		return abs($this->getPosition()->get('x')) + abs($this->getPosition()->get('y')) + abs($this->getPosition()->get('z'));
	}
	
	
	public function getKineticEnergy(): int
	{
		return abs($this->getVelocity()->get('x')) + abs($this->getVelocity()->get('y')) + abs($this->getVelocity()->get('z'));
	}
	
	
	public function __toString()
	{
		return "pos=<x=".str_pad($this->getPosition()->get('x'), 3, ' ', STR_PAD_LEFT).", y=".str_pad($this->getPosition()->get('y'), 3, ' ', STR_PAD_LEFT).", z=".str_pad($this->getPosition()->get('z'), 3, ' ', STR_PAD_LEFT).">, ".
			"vel=<x=".str_pad($this->getVelocity()->get('x'), 3, ' ', STR_PAD_LEFT).", y=".str_pad($this->getVelocity()->get('y'), 3, ' ', STR_PAD_LEFT).", z=".str_pad($this->getVelocity()->get('z'), 3, ' ', STR_PAD_LEFT).">";
	}
}


class MoonCoordinate
{
	private $coordinates = [];
	
	
	public function __construct(int $x, int $y, int $z)
	{
		$this->coordinates = [
			'x' => $x,
			'y' => $y,
			'z' => $z
		];
	}
	
	
	public function add(string $axis, int $add): void
	{
		$this->coordinates[$axis] += $add;
	}
	
	
	public function get(string $axis): int
	{
		return $this->coordinates[$axis];
	}
	
	
	public static function parse($data): self
	{
		preg_match('#x=(\-?\d+),\sy=(\-?\d+),\sz=(\-?\d+)#', $data, $matches);
		return new self($matches[1], $matches[2], $matches[3]);
	}
}
