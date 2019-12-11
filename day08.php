<?php

$input = file_get_contents('resources/day08.txt');


$image = new SpaceImage(25, 6);
$image->readStream($input);

$layer = $image->getLayerContainingLowestDigitCount(0);


echo count($image->getLayers())." layers found\n";
echo "Result of part 1: ".($layer->getDigitCount(1) * $layer->getDigitCount(2))."\n";




class SpaceImage
{
	private $width = 0;
	private $height = 0;
	private $layers = [];
	private $data = '';
	
	
	public function __construct(int $width, int $height)
	{
		$this->width = $width;
		$this->height = $height;
	}
	
	
	public function readStream(string $imageData): void
	{
		$this->data = $imageData;
		
		foreach (str_split($imageData, $this->width * $this->height) as $layerData) {
			$this->layers[] = new Layer(str_split($layerData, $this->width));
		}
	}
	
	
	public function getLayers(): array
	{
		return $this->layers;
	}

	
	public function getLayerContainingLowestDigitCount($digit): Layer
	{
		$lowestLayer = null;
		$lowestCount = null;
		
		foreach ($this->getLayers() as $layer) {
			$digitCount = $layer->getDigitCount($digit);
			
			if ($lowestCount === null || $digitCount < $lowestCount) {
				$lowestCount = $digitCount;
				$lowestLayer = $layer;
			}
		}
		
		return $lowestLayer;
	}
	
}


class Layer
{
	private $lines = [];
	
	
	public function __construct(array $lines)
	{
		$this->lines = $lines;
	}
	
	
	public function getDigitCount($digit)
	{
		$total = 0;
		
		foreach ($this->lines as $line) {
			$total += substr_count($line, $digit);
		}
		
		return $total;
	}
	
	
	public function __toString()
	{
		return join("\n", $this->lines);
	}
}



class Pixel
{
	const COLOR_BLACK = 0;
	const COLOR_WHITE = 1;
	const COLOR_TRANSPARENT = 2;
}
