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



$map     = new AstroidMap($input);
$astroid = $map->getBestLocationAstroid();

echo "Best location astroid: {$astroid->getCoordinates()} (".count($map->getVisibleAstroidsFrom($astroid))." visible astroids from this point)\n";




class AstroidMap
{
	private $astroids = [];
	
	
	public function __construct($input)
	{
		foreach (explode("\n", $input) as $y => $row) {
			foreach (str_split($row) as $x => $point) {
				if ($point == '#') {
					$this->astroids[] = new Astroid($x, $y);
				}
			}
		}
	}
	
	
	public function getBestLocationAstroid(): Astroid
	{
		/**
		 * @var Astroid $bestLocationAstroid
		 * @var Astroid $sourceAstroid
		 */
		$bestLocationAstroid = null;
		$maxVisibleLocations = 0;
		
		foreach ($this->astroids as $sourceAstroid) {
			$visibleAstroids = $this->getVisibleAstroidsFrom($sourceAstroid);
			
			if (count($visibleAstroids) > $maxVisibleLocations) {
				$maxVisibleLocations = count($visibleAstroids);
				$bestLocationAstroid = $sourceAstroid;
			}
		}
		
		return $bestLocationAstroid;
	}
	
	
	
	public function getVisibleAstroidsFrom(Astroid $astroid): array
	{
		$x = $astroid->getX();
		$y = $astroid->getY();
		
		$astroids = [];
		
		foreach ($this->sortAstroidsByDistance($this->astroids, $x, $y) as $targetAstroid) {
			$angle = $targetAstroid->getAngleFrom($x, $y);
			
			if ($targetAstroid->getX() == $x && $targetAstroid->getY() == $y) {
				continue;
			}
			
			if (!isset($astroids[(string)$angle])) {
				$astroids[(string)$angle] = $targetAstroid;
			}
		}
		
		return $astroids;
	}
	
	
	
	private function sortAstroidsByDistance(array $astroids, int $fromX, int $fromY): array
	{
		uasort($astroids, function($a, $b) use ($fromX, $fromY) {
			/**
			 * @var Astroid $a
			 * @var Astroid $b
			 */
			$distanceA = $a->getDistanceFrom($fromX, $fromY);
			$distanceB = $b->getDistanceFrom($fromX, $fromY);
			
			if ($distanceA == $distanceB) return 0;
			return $distanceA < $distanceB ? -1 : 1;
		});
		
		return $astroids;
	}
	
	
	public function getAstroids()
	{
		return $this->astroids;
	}
}


class Astroid
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
