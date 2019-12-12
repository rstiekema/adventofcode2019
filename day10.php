<?php

$input = file_get_contents('resources/day10.txt');

// Best location: 3,4
//$input = trim("
//.#..#
//.....
//#####
//....#
//...##");

// Best location: 5,8
//$input = trim("
//......#.#.
//#..#.#....
//..#######.
//.#.#.###..
//.#..#.....
//..#....#.#
//#..#....#.
//.##.#..###
//##...#..#.
//.#....####");

// Best location: 1,2
//$input = trim("
//#.#...#.#.
//.###....#.
//.#....#...
//##.#.#.#.#
//....#.#.#.
//.##..###.#
//..#...##..
//..##....##
//......#...
//.####.###.");

// Best location: 6,3
//$input = trim("
//.#..#..###
//####.###.#
//....###.#.
//..###.##.#
//##.##.#.#.
//....###..#
//..#.#..#.#
//#..#.#.###
//.##...##.#
//.....#.#..");

// Best location: 11,13
//$input = trim("
//.#..##.###...#######
//##.############..##.
//.#.######.########.#
//.###.#######.####.#.
//#####.##.#.##.###.##
//..#####..#.#########
//####################
//#.####....###.#.#.##
//##.#################
//#####.##.###..####..
//..######..##.#######
//####.##.####...##..#
//.#####..#.######.###
//##...#.##########...
//#.##########.#######
//.####.#.###.###.#.##
//....##.##.###..#####
//.#.#.###########.###
//#.#.#.#####.####.###
//###.##.####.##.#..##");



$map  = new AsteroidMap($input);


$asteroid = $map->getBestLocationAsteroid();
echo "Best location asteroid: {$asteroid->getCoordinates()} (".count($map->getVisibleAsteroidsFrom($asteroid))." visible asteroids from this point)\n";

$asteroids = $map->getAsteroids();
echo "Total asteroids: ".count($map->getAsteroids())."\n";

$vaporizeOrder = $map->getVaporizeOrder($asteroid);
$vaporize200th = $vaporizeOrder[199];
echo "200th asteroid of ".count($vaporizeOrder)." to be vaporized: ".$vaporizeOrder[199]."\n";
echo "Part 2 result: ".($vaporize200th->getX() * 100 + $vaporize200th->getY())."\n";

//echo "Vaporize order:\n";
//foreach ($vaporizeOrder as $key => $vaporizeAsteroid) {
//	echo ($key + 1).": ".str_pad($vaporizeAsteroid->getCoordinates(), 9)." (".$vaporizeAsteroid->getAngleFrom($asteroid->getX(), $asteroid->getY())."deg)\n";
//}



class AsteroidMap
{
	private $asteroids = [];
	
	
	public function __construct($input)
	{
		foreach (explode("\n", $input) as $y => $row) {
			foreach (str_split($row) as $x => $point) {
				if ($point == '#') {
					$this->asteroids[] = new Asteroid($x, $y);
				}
			}
		}
	}
	
	
	public function getBestLocationAsteroid(): Asteroid
	{
		/**
		 * @var Asteroid $bestLocationAsteroid
		 * @var Asteroid $sourceAsteroid
		 */
		$bestLocationAsteroid = null;
		$maxVisibleLocations = 0;
		
		foreach ($this->asteroids as $sourceAsteroid) {
			$visibleAsteroids = $this->getVisibleAsteroidsFrom($sourceAsteroid);
			
			if (count($visibleAsteroids) > $maxVisibleLocations) {
				$maxVisibleLocations = count($visibleAsteroids);
				$bestLocationAsteroid = $sourceAsteroid;
			}
		}
		
		return $bestLocationAsteroid;
	}
	
	
	public function getVisibleAsteroidsFrom(Asteroid $asteroid, array $asteroids = null): array
	{
		$x = $asteroid->getX();
		$y = $asteroid->getY();
		
		if ($asteroids === null) {
			$asteroids = $this->getAsteroids();
		}
		
		$visible = [];
		
		foreach ($this->sortAsteroidsByDistance($asteroids, $x, $y) as $targetAsteroid) {
			$angle = $targetAsteroid->getAngleFrom($x, $y);
			
			if ($targetAsteroid->getX() == $x && $targetAsteroid->getY() == $y) {
				continue;
			}
			
			if (!isset($visible[(string)$angle])) {
				$visible[(string)$angle] = $targetAsteroid;
			}
		}
		
		return array_values($visible);
	}
	
	
	public function getVaporizeOrder(Asteroid $from): array
	{
		$sourceX   = $from->getX();
		$sourceY   = $from->getY();
		$asteroids = $this->getAsteroids();
		
		// Let's not vaporize our source asteroid...
		foreach ($asteroids as $asteroidKey => $asteroid) {
			if ($asteroid->getX() == $sourceX && $asteroid->getY() == $sourceY) {
				unset($asteroids[$asteroidKey]);
				break;
			}
		}
		
		$vaporizeOrder = [];
		
		while (!empty($asteroids)) {
			$visible       = $this->getVisibleAsteroidsFrom($from, $asteroids);
			$visible       = $this->sortAsteroidsByAngle($visible, $sourceX, $sourceY);
			$vaporizeOrder = array_merge($vaporizeOrder, $visible);
			$asteroids     = array_diff($asteroids, $visible);
		}
		
		return $vaporizeOrder;
	}
	
	
	private function sortAsteroidsByDistance(array $asteroids, int $fromX, int $fromY): array
	{
		uasort($asteroids, function($a, $b) use ($fromX, $fromY) {
			/**
			 * @var Asteroid $a
			 * @var Asteroid $b
			 */
			$distanceA = $a->getDistanceFrom($fromX, $fromY);
			$distanceB = $b->getDistanceFrom($fromX, $fromY);
			
			if ($distanceA == $distanceB) return 0;
			return $distanceA < $distanceB ? -1 : 1;
		});
		
		return $asteroids;
	}
	
	
	private function sortAsteroidsByAngle(array $asteroids, int $fromX, int $fromY): array
	{
		uasort($asteroids, function($a, $b) use ($fromX, $fromY) {
			/**
			 * @var Asteroid $a
			 * @var Asteroid $b
			 */
			$angleA = $a->getAngleFrom($fromX, $fromY);
			$angleB = $b->getAngleFrom($fromX, $fromY);
			
			if ($angleA == $angleB) return 0;
			return $angleA < $angleB ? -1 : 1;
		});
		
		return $asteroids;
	}
	
	
	public function getAsteroids()
	{
		return $this->asteroids;
	}
}


class Asteroid
{
	private $x;
	private $y;
	
	
	public function __construct(int $x, int $y)
	{
		$this->x = $x;
		$this->y = $y;
	}
	
	
	public function getX(): int
	{
		return $this->x;
	}
	
	
	public function getY(): int
	{
		return $this->y;
	}
	
	
	public function getCoordinates(): string
	{
		return "{$this->x},{$this->y}";
	}
	
	
	public function getDistanceFrom(int $x, int $y): float
	{
		return sqrt(($x - $this->x) ** 2 + ($y - $this->y) ** 2);
	}
	
	
	public function getAngleFrom(int $x, int $y): float
	{
		$angle = atan2($y - $this->y, $x - $this->x) * 180 / pi();
		
		// 'Up' should be 0 degrees...
		$angle -= 90;
		if ($angle < 0) $angle += 360;
		
		return $angle;
	}
	
	
	public function __toString()
	{
		return $this->getCoordinates();
	}
	
}
