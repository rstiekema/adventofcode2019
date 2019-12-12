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



$map     = new AsteroidMap($input);
$asteroid = $map->getBestLocationAsteroid();

echo "Best location asteroid: {$asteroid->getCoordinates()} (".count($map->getVisibleAsteroidsFrom($asteroid))." visible asteroids from this point)\n";




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
	
	
	
	public function getVisibleAsteroidsFrom(Asteroid $asteroid): array
	{
		$x = $asteroid->getX();
		$y = $asteroid->getY();
		
		$asteroids = [];
		
		foreach ($this->sortAsteroidsByDistance($this->asteroids, $x, $y) as $targetAsteroid) {
			$angle = $targetAsteroid->getAngleFrom($x, $y);
			
			if ($targetAsteroid->getX() == $x && $targetAsteroid->getY() == $y) {
				continue;
			}
			
			if (!isset($asteroids[(string)$angle])) {
				$asteroids[(string)$angle] = $targetAsteroid;
			}
		}
		
		return $asteroids;
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
		return atan2($y - $this->y, $x - $this->x) * 180 / pi();
	}
	
}
