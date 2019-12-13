<?php

require __DIR__.'/bootstrap.php';


$program = explode(',', file_get_contents('resources/day11.txt'));



// Part 1
$painter = new HullPainter();
$painter->setPaintProgram($program);

$startingPanel = new Panel(0, 0);
$startingPanel->setColor(0);

$painter->paintFrom($startingPanel);

echo count($painter->getPanels())." panels painted\n";
echo "Painting:\n";
echo $painter->getPainting();



// Part 2
$painter = new HullPainter();
$painter->setPaintProgram($program);

$startingPanel = new Panel(0, 0);
$startingPanel->setColor(1);

$painter->paintFrom($startingPanel);
$panels = $painter->getPanels();

echo count($painter->getPanels())." panels painted\n";
echo "Painting:\n";
echo $painter->getPainting();






class HullPainter
{
	
	private $computer;
	private $computerOutput = [];
	
	private $panels = [];
	
	private $currentX = 0;
	private $currentY = 0;
	private $currentDirection = 1;
	
	
	public function setPaintProgram($program)
	{
		$memory = new \IntcodeComputer\Memory($program);
		$this->computer = new IntcodeComputer\IntcodeComputer($memory);
		
		$this->computer->setInputCallback([$this, 'setComputerInput']);
		$this->computer->setOutputCallback([$this, 'checkComputerOutput']);
	}
	
	
	public function setComputerInput()
	{
		$panel = $this->getCurrentPanel();
		return $panel->getColor();
	}
	
	
	public function checkComputerOutput($output): void
	{
		$this->computerOutput[] = $output;
		
		if (count($this->computerOutput) < 2) {
			return;
		}
		
		$this->getCurrentPanel()->setColor($this->computerOutput[0]);
		$this->move($this->computerOutput[1]);
		
		$this->computerOutput = [];
	}
	
	
	private function move($direction)
	{
		if ($direction == 0) {
			// 0 = turn left
			$this->currentDirection--;
			
		} else {
			// 1 = turn right
			$this->currentDirection++;
		}
		
		if ($this->currentDirection < 1) {
			$this->currentDirection = 4;
		} else if ($this->currentDirection > 4) {
			$this->currentDirection = 1;
		}
		
		if ($this->currentDirection == 1) $this->currentY--; // up
		if ($this->currentDirection == 2) $this->currentX++; // right
		if ($this->currentDirection == 3) $this->currentY++; // down
		if ($this->currentDirection == 4) $this->currentX--; // left
	}
	
	
	private function getCurrentPanel(): Panel
	{
		$panelKey = "{$this->currentX},{$this->currentY}";
		
		if (!isset($this->panels[$panelKey])) {
			$this->panels[$panelKey] = new Panel($this->currentX, $this->currentY);
		}
		
		return $this->panels[$panelKey];
	}
	
	
	public function paintFrom(Panel $panel)
	{
		$this->currentX = $panel->getX();
		$this->currentY = $panel->getY();
		
		$panelKey = "{$this->currentX},{$this->currentY}";
		$this->panels[$panelKey] = $panel;
		
		$this->computer->run();
	}
	
	
	public function getPainting(): string
	{
		$panels       = $this->getPanels();
		$minMaxCoords = $this->getMinMaxCoordinates();
		$painting     = '';
		
		for ($y = $minMaxCoords['minY']; $y <= $minMaxCoords['maxY']; $y++) {
			for ($x = $minMaxCoords['minX']; $x <= $minMaxCoords['maxX']; $x++) {
				$panelKey = "{$x},{$y}";
				$color    = isset($panels[$panelKey]) ? $panels[$panelKey]->getColor() : '';
				
				if (!isset($panels[$panelKey])) {
					$painting .= ' ';
				} else if ($color == 1) {
					$painting .= "\e[47m \e[0m";
				} else {
					$painting .= "\e[40m \e[0m";
				}
			}
			
			$painting .= "\n";
		}
		
		return $painting;
	}
	
	
	private function getMinMaxCoordinates(): array
	{
		$minMax = [
			'minX' => 0,
			'minY' => 0,
			'maxX' => 0,
			'maxY' => 0
		];
		
		foreach ($this->getPanels() as $panel) {
			$panelX = $panel->getX();
			$panelY = $panel->getY();
			
			if ($panelX < $minMax['minX']) $minMax['minX'] = $panelX;
			if ($panelY < $minMax['minY']) $minMax['minY'] = $panelX;
			if ($panelX > $minMax['maxX']) $minMax['maxX'] = $panelX;
			if ($panelY > $minMax['maxY']) $minMax['maxY'] = $panelY;
		}
		
		return $minMax;
	}
	
	
	public function getPanels()
	{
		return $this->panels;
	}
}


class Panel
{
	private $x = 0;
	private $y = 0;
	private $color = 0;
	
	
	public function __construct(int $x, int $y)
	{
		$this->x = $x;
		$this->y = $y;
	}
	
	
	public function getCoordinates(): string
	{
		return "{$this->x},{$this->y}";
	}
	
	
	public function setX(int $x): void
	{
		$this->x = $x;
	}
	
	
	public function setY(int $y): void
	{
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
	
	
	public function setColor($color): void
	{
		$this->color = $color;
	}
	
	
	public function getColor(): int
	{
		return $this->color;
	}
}
