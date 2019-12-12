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



class LayerMerger
{
	
	private $merged = [];
	
	
	public function addLayer(Layer $layer): void
	{
		/**
		 * @var Pixel $pixel
		 */
		
		foreach ($layer->getLines() as $lineKey => $line) {
			foreach ($line as $pixelKey => $pixel) {
				if (!isset($this->merged[$lineKey][$pixelKey]) || $this->merged[$lineKey][$pixelKey]->getType() == Pixel::TYPE_TRANSPARENT) {
					$this->merged[$lineKey][$pixelKey] = $pixel;
				}
			}
		}
	}
	
	
	public function getMergedLines(): array
	{
		$lines = [];
		
		foreach ($this->merged as $pixels) {
			$line = '';
			
			foreach ($pixels as $pixel) {
				$line .= $pixel;
			}
			
			$lines[] = $line;
		}
		
		return $lines;
	}
}



class Layer
{
	private $lineData = [];
	
	
	public function __construct(array $lineData)
	{
		$this->lineData = $lineData;
	}
	
	
	public function getDigitCount($digit)
	{
		$total = 0;
		
		foreach ($this->lineData as $line) {
			$total += substr_count($line, $digit);
		}
		
		return $total;
	}
	
	
	public function __toString()
	{
		return join("\n", $this->lineData);
	}
	
	
	public function getLines()
	{
		$lines = [];
		
		foreach ($this->lineData as $pixelData) {
			$lines[] = new Pixel($pixelData);
		}
		
		return $lines;
	}
}



class Pixel
{
	const COLOR_BLACK = 0;
	const COLOR_WHITE = 1;
	const COLOR_TRANSPARENT = 2;
	
	const TYPE_TRANSPARENT = 1;
	const TYPE_COLOR = 2;
	
	private $color = 0;
	
	
	public function __construct(int $color)
	{
		$this->color = $color;
	}
	
	
	public function getType()
	{
		if ($this->color == self::COLOR_TRANSPARENT) {
			return self::TYPE_TRANSPARENT;
		}
		
		return self::TYPE_COLOR;
	}
	
	
	public function __toString()
	{
		return (string)$this->color;
	}
}
