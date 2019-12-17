<?php

require __DIR__.'/bootstrap.php';


$program = explode(',', file_get_contents('resources/day15.txt'));


$repairDroid = new RepairDroid($program);
$repairDroid->start();

echo "Found target at {$repairDroid->getCurrentCoordinate()}\n";



class RepairDroid
{
	/**
	 * @var \IntcodeComputer\IntcodeComputer
	 */
	private $computer;
	
	/**
	 * @var RepairDroidCoordinate
	 */
	private $currentCoordinate;
	
	private $coordinates = [];
	private $currentDirection = 1;
	
	
	public function __construct($program)
	{
		$memory = new \IntcodeComputer\Memory($program);
		$this->computer = new IntcodeComputer\IntcodeComputer($memory);
		
		$this->computer->setInputCallback([$this, 'setComputerInput']);
		$this->computer->setOutputCallback([$this, 'checkComputerOutput']);
	}
	
	
	public function start(): void
	{
		$this->currentCoordinate = new RepairDroidCoordinate(0, 0);
		$this->computer->run();
	}
	
	
	public function setComputerInput()
	{
		$this->paint();
//		usleep(4000);
		return $this->currentDirection;
	}
	
	
	private function paint()
	{
		echo "\033[2J\033[H";
		echo $this->getPainting()."\n";
		echo "current direction: ".$this->currentDirection."\n";
	}
	
	
	public function checkComputerOutput($output): void
	{
		echo "\n";
		echo "output: $output\n";
		
		$this->getTargetCoordinate()->setType($output);
		
		switch ($output) {
			case RepairDroidCoordinate::TYPE_WALL:
				// Hit a wall, change direction
				$this->changeDirection();
				break;
				
			case RepairDroidCoordinate::TYPE_EMPTY:
			case RepairDroidCoordinate::TYPE_TARGET:
				// Position changed
				$this->currentCoordinate = $this->getTargetCoordinate();
				$this->changeDirection();
				break;
				
//			case RepairDroidCoordinate::TYPE_TARGET:
//				// Found the target
//				$this->computer->exit();
//				$this->paint();
//				break;
		}
	}
	
	
	public function changeDirection(): void
	{
		// Sort the coordinates in all directions by coordinate type
		$targets = [
			1 => $this->getTargetCoordinate(1),
			2 => $this->getTargetCoordinate(2),
			3 => $this->getTargetCoordinate(3),
			4 => $this->getTargetCoordinate(4),
		];
		
		// Group by coordinate type
		$typeTargets = [];
		
		foreach ($targets as $direction => $target) {
			$typeTargets[$target->getType()][$direction] = $target;
		}
		
		// If 3 sides are walls or dead ends, mark the current coordinate as a dead end
		$wallCoordinates    = isset($typeTargets[RepairDroidCoordinate::TYPE_WALL]) ? count($typeTargets[RepairDroidCoordinate::TYPE_WALL]) : 0;
		$deadEndCoordinates = isset($typeTargets[RepairDroidCoordinate::TYPE_DEADEND]) ? count($typeTargets[RepairDroidCoordinate::TYPE_DEADEND]) : 0;
		
		if ($wallCoordinates + $deadEndCoordinates == 3) {
			$this->currentCoordinate->setType(RepairDroidCoordinate::TYPE_DEADEND);
		}
		
		// Sort by type (highest first)
		krsort($typeTargets);
		$firstTargets = array_shift($typeTargets);
		
		// Set new direction
		$this->currentDirection = array_rand($firstTargets);
	}
	
	
	public function getTargetCoordinate($direction = null): RepairDroidCoordinate
	{
		$x = $this->currentCoordinate->getX();
		$y = $this->currentCoordinate->getY();
		
		if ($direction === null) {
			$direction = $this->currentDirection;
		}
		
		switch ($direction) {
			case RepairDroidCoordinate::DIRECTION_NORTH: return $this->getCoordinate($x, $y - 1); // north
			case RepairDroidCoordinate::DIRECTION_SOUTH: return $this->getCoordinate($x, $y + 1); // south
			case RepairDroidCoordinate::DIRECTION_WEST:  return $this->getCoordinate($x - 1, $y); // west
			case RepairDroidCoordinate::DIRECTION_EAST:  return $this->getCoordinate($x + 1, $y); // east
		}
	}
	
	
	public function getCoordinate($x, $y): RepairDroidCoordinate
	{
		$key = "{$x},{$y}";
		
		if (!isset($this->coordinates[$key])) {
			echo "creating new coordinate $key\n";
			$this->coordinates[$key] = new RepairDroidCoordinate($x, $y);
		}
		
		return $this->coordinates[$key];
	}
	
	
	public function getPainting(): string
	{
		$minMaxCoords = $this->getMinMaxCoordinates();
		$painting     = '';
		
		for ($y = $minMaxCoords['minY']; $y <= $minMaxCoords['maxY']; $y++) {
			for ($x = $minMaxCoords['minX']; $x <= $minMaxCoords['maxX']; $x++) {
				$key        = "{$x},{$y}";
				$coordinate = isset($this->coordinates[$key]) ? $this->coordinates[$key] : null;
				
				if ($coordinate === null) {
					$painting .= ' ';
					
				} else if ($coordinate == $this->currentCoordinate) {
					if ($this->currentDirection == 1) $painting .= '^'; // north
					if ($this->currentDirection == 2) $painting .= 'v'; // south
					if ($this->currentDirection == 3) $painting .= '<'; // west
					if ($this->currentDirection == 4) $painting .= '>'; // east
					
				} else if ($coordinate->getType() == RepairDroidCoordinate::TYPE_WALL) {
					$painting .= '#';
					
				} else if ($coordinate->getType() == RepairDroidCoordinate::TYPE_EMPTY) {
					$painting .= '.';
					
				} else if ($coordinate->getType() == RepairDroidCoordinate::TYPE_DEADEND) {
					$painting .= ' ';
					
				} else if ($coordinate->getType() == RepairDroidCoordinate::TYPE_TARGET) {
					$painting .= 'D';
					
				} else {
					$painting .= ' ';
				}
			}
			
			$painting .= "\n";
		}
		
		return $painting;
	}
	
	
	private function getMinMaxCoordinates(): array
	{
		$minMax = [
			'minX' => -40,
			'minY' => -40,
			'maxX' => 40,
			'maxY' => 40
		];
		
//		$minMax = [
//			'minX' => 0,
//			'minY' => 0,
//			'maxX' => 0,
//			'maxY' => 0
//		];
		
		foreach ($this->coordinates as $coordinate) {
			$x = $coordinate->getX();
			$y = $coordinate->getY();
			
			if ($x < $minMax['minX']) $minMax['minX'] = $x;
			if ($y < $minMax['minY']) $minMax['minY'] = $x;
			if ($x > $minMax['maxX']) $minMax['maxX'] = $x;
			if ($y > $minMax['maxY']) $minMax['maxY'] = $y;
		}
		
		return $minMax;
	}
	
	
	public function getCurrentCoordinate()
	{
		return $this->currentCoordinate;
	}
}



class RepairDroidCoordinate
{
	const TYPE_DEADEND = -1;
	const TYPE_WALL    = 0;
	const TYPE_EMPTY   = 1;
	const TYPE_TARGET  = 2;
	const TYPE_NOTSET  = 99;
	
	const DIRECTION_NORTH = 1;
	const DIRECTION_SOUTH = 2;
	const DIRECTION_WEST  = 3;
	const DIRECTION_EAST  = 4;
	
	private $x = 0;
	private $y = 0;
	private $type = -1;
	private $disabledDirections = [];
	
	
	public function __construct(int $x, int $y)
	{
		$this->x = $x;
		$this->y = $y;
		$this->type = self::TYPE_NOTSET;
	}
	
	
	public function getX(): int
	{
		return $this->x;
	}
	
	
	public function getY(): int
	{
		return $this->y;
	}
	
	
	public function getType(): int
	{
		return $this->type;
	}
	
	
	public function setType($type): void
	{
		if ($this->type == self::TYPE_TARGET) {
			return;
		}
		
		if ($this->type != self::TYPE_NOTSET && $type != self::TYPE_DEADEND && $type != self::TYPE_TARGET) {
			return;
		}
		
		$this->type = $type;
	}
	
	
	public function disableDirection($direction): void
	{
		$this->disabledDirections[] = $direction;
	}
	
	
	public function isDirectionDisabled($direction): bool
	{
		return in_array($direction, $this->disabledDirections);
	}
	
	
	public function __toString()
	{
		return "{$this->x},{$this->y}";
	}
	
}
