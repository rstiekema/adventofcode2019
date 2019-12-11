<?php

$input = file_get_contents('resources/day03.txt');
$grid  = new Grid();

foreach (explode("\n", $input) as $path) {
	$wire = new Wire();
	$wire->setPath($path);
	$grid->addWire($wire);
}

$sourceCoordinate              = new Coordinate(0, 0);
$closestIntersectionByDistance = $grid->getClosestIntersectionByDistanceFrom($sourceCoordinate);
$closestIntersectionBySteps    = $grid->getClosestIntersectionByStepsFrom($sourceCoordinate);

echo "closest intersection by distance from $sourceCoordinate is $closestIntersectionByDistance, distance: ".$closestIntersectionByDistance->getDistanceFrom($sourceCoordinate)."\n";
echo "closest intersection by steps from $sourceCoordinate is $closestIntersectionBySteps (".$closestIntersectionBySteps->steps." steps)\n";




class Grid
{
	private $wires = [];
	
	
	public function addWire(Wire $wire): void
	{
		$this->wires[] = $wire;
	}
	
	
	public function getIntersections(): array
	{
		$allCoordinates = [];
		
		foreach ($this->wires as $wire) {
			/** @var Wire $wire */
			$allCoordinates[] = $wire->getCoordinates();
		}
		
		$intersections = array_intersect(... $allCoordinates);
		
		foreach ($intersections as $key => $coordinate) {
			/** @var Coordinate $coordinate */
			if ($coordinate->x == 0 && $coordinate->y == 0) {
				unset($intersections[$key]);
			}
		}
		
		return $intersections;
	}
	
	
	public function getClosestIntersectionByDistanceFrom(Coordinate $from):? Coordinate
	{
		$closestIntersection = null;
		$smallestDistance    = null;
		
		foreach ($this->getIntersections() as $intersection) {
			/** @var Coordinate $intersection */
			$distance = $intersection->getDistanceFrom($from);
			
			if ($smallestDistance === null || $distance < $smallestDistance) {
				$closestIntersection = $intersection;
				$smallestDistance    = $distance;
			}
		}
		
		return $closestIntersection;
	}
	
	
	public function getClosestIntersectionByStepsFrom(Coordinate $from):? Coordinate
	{
		$intersections     = $this->getIntersections();
		$intersectionSteps = [];
		
		/**
		 * @var Wire $wire
		 * @var Coordinate $intersection
		 */
		foreach ($intersections as $intersectionKey => $intersection) {
			$intersectionSteps[$intersectionKey] = 0;
			
			foreach ($this->wires as $wireKey => $wire) {
				$intersectionSteps[$intersectionKey] += $wire->getStepsBetween($from, $intersection);
			}
		}
		
		$minSteps            = min($intersectionSteps);
		$closestIntersection = $intersections[array_search($minSteps, $intersectionSteps)];
		
		$closestIntersection->steps = $minSteps;
		
		return $closestIntersection;
	}
}



class WireDirection
{
	const DIRECTION_RIGHT = 'R';
	const DIRECTION_LEFT  = 'L';
	const DIRECTION_UP    = 'U';
	const DIRECTION_DOWN  = 'D';
	
	/**
	 * @var Coordinate
	 */
	private $sourceCoordinate;
	
	/**
	 * @var array
	 */
	private $coordinates = [];
	
	
	public function setSourceCoordinate(Coordinate $coordinate): void
	{
		$this->sourceCoordinate = $coordinate;
	}
	
	
	public function getFinalCoordinate() :? Coordinate
	{
		if (empty($this->coordinates)) {
			return null;
		}
		
		return $this->coordinates[array_reverse(array_keys($this->coordinates))[0]];
	}
	
	
	/**
	 * @param string $instruction
	 */
	public function setInstruction(string $instruction): void
	{
		$direction = substr($instruction, 0, 1);
		$length    = substr($instruction, 1);
		
		$sourceX   = $this->sourceCoordinate->x;
		$sourceY   = $this->sourceCoordinate->y;
		
		switch ($direction) {
			case self::DIRECTION_DOWN:
				for ($i = $sourceY; $i <= $sourceY + $length; $i++) {
					$this->addCoordinate($sourceX, $i);
				}
				break;
				
			case self::DIRECTION_UP:
				for ($i = $sourceY; $i >= $sourceY - $length; $i--) {
					$this->addCoordinate($sourceX, $i);
				}
				break;
				
			case self::DIRECTION_RIGHT:
				for ($i = $sourceX; $i <= $sourceX + $length; $i++) {
					$this->addCoordinate($i, $sourceY);
				}
				break;
				
			case self::DIRECTION_LEFT:
				for ($i = $sourceX; $i >= $sourceX - $length; $i--) {
					$this->addCoordinate($i, $sourceY);
				}
				break;
		}
	}
	
	
	public function addCoordinate(int $x, int $y): void
	{
		$this->coordinates[] = new Coordinate($x, $y);
	}
	
	
	public function getCoordinates(): array
	{
		return $this->coordinates;
	}
}


class Wire
{
	/**
	 * @var array
	 */
	private $directions = [];
	
	/**
	 * @var array
	 */
	private $coordinates = [];
	
	
	/**
	 * @param string $path
	 */
	public function setPath(string $path): void
	{
		$sourceCoordinate = new Coordinate(0, 0);
		
		foreach (explode(',', $path) as $instruction) {
			$direction = new WireDirection();
			$direction->setSourceCoordinate($sourceCoordinate);
			$direction->setInstruction($instruction);
			
			$sourceCoordinate = $direction->getFinalCoordinate();
			
			$this->directions[] = $direction;
		}
	}
	
	
	/**
	 * @return array
	 */
	public function getCoordinates(): array
	{
		$coordinates = [];
		
		if (!empty($this->coordinates)) {
			return $this->coordinates;
		}
		
		foreach ($this->directions as $direction) {
			/** @var WireDirection $direction */
			$coordinates = array_merge($coordinates, $direction->getCoordinates());
			
		}
		
		$this->coordinates = $coordinates;
		
		return $this->coordinates;
	}
	
	
	/**
	 * @param Coordinate $from
	 * @param Coordinate $to
	 * @return int|null
	 */
	public function getStepsBetween(Coordinate $from, Coordinate $to):? int
	{
		$steps = null;
		$prevCoordinate = null;
		
		/**
		 * @var WireDirection $direction
		 * @var Coordinate $coordinate
		 * @var Coordinate $prevCoordinate
		 */
		foreach ($this->getCoordinates() as $coordinate) {
			if ($coordinate->__toString() == $from->__toString()) {
				$steps = 0;
			}
			
			if ($coordinate->__toString() == $to->__toString()) {
				break;
			}
			
			if ($prevCoordinate !== null && $prevCoordinate->__tostring() == $coordinate->__toString()) {
				continue;
			}
			
			if ($steps !== null) {
				$steps++;
			}
			
			$prevCoordinate = $coordinate;
		}
		
		return $steps;
	}
	
}


class Coordinate
{
	public $x = 0;
	public $y = 0;
	public $steps = 0;
	
	
	/**
	 * GridCoordinate constructor.
	 * @param int $x
	 * @param int $y
	 */
	public function __construct(int $x, int $y)
	{
		$this->x = $x;
		$this->y = $y;
	}
	
	/**
	 * @return string
	 */
	public function __toString()
	{
		return "{$this->x},{$this->y}";
	}
	
	
	public function getDistanceFrom(Coordinate $from): int
	{
		return abs($this->x - $from->x) + abs($this->y - $from->y);
	}
}
