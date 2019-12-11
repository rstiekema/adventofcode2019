<?php

$input = explode("\n", file_get_contents('resources/day06.txt'));
$map   = new OrbitMap($input);

echo "Total number of orbits: ".$map->getTotalOrbits()."\n";
echo "Orbital transfers between YOU and SAN: ".$map->getOrbitalTransfersBetween('YOU', 'SAN')."\n";



class OrbitMap
{
	
	private $objects       = [];
	private $parentObjects = [];
	
	
	public function __construct($map)
	{
		foreach ($map as $item) {
			list($parentObject, $object) = explode(')', $item);
			
			if (!isset($this->parentObjects[$parentObject])) {
				$this->parentObjects[$parentObject] = [];
			}
			
			$this->objects[$object] = [
				'parent' => $parentObject
			];
			
			$this->parentObjects[$parentObject][] = $object;
		}
	}
	
	
	public function getParentObject(string $object):? string
	{
		if (isset($this->objects[$object])) {
			return $this->objects[$object]['parent'];
		}

		return null;
	}
	
	
	public function getAllParentObjects(string $object): array
	{
		$parentObjects = array();

		while ($object = $this->getParentObject($object)) {
			$parentObjects[] = $object;
		}
		
		return $parentObjects;
	}
	
	
	public function getTotalOrbits(): int
	{
		$total = 0;
		
		foreach ($this->objects as $object => $objectData) {
			$total += count($this->getAllParentObjects($object));
		}
		
		return $total;
	}
	
	
	public function getOrbitalTransfersBetween(string $from, string $to): int
	{
		$fromParents   = $this->getAllParentObjects($from);
		$toParents     = $this->getAllParentObjects($to);
		$sharedParents = array_intersect($fromParents, $toParents);
		
		return count($fromParents) + count($toParents) - count($sharedParents) * 2;
	}
}
