<?php

require __DIR__.'/bootstrap.php';


$program = explode(',', file_get_contents('resources/day13.txt'));


$game = new ArcadeGame($program);
$game->start();


// Part 1
echo count($game->getTilesById(Tile::ID_BLOCK))." block tiles\n";


// Part 2
echo "Game over! Final score: {$game->getScore()}\n";



class ArcadeGame
{
	/**
	 * @var \IntcodeComputer\IntcodeComputer
	 */
	private $computer;
	
	/**
	 * @var \IntcodeComputer\Memory
	 */
	private $memory;
	
	private $computerOutput = [];
	
	private $tiles = [];
	
	private $score = 0;
	
	
	public function __construct($program)
	{
		$this->memory = new \IntcodeComputer\Memory($program);
		$this->computer = new IntcodeComputer\IntcodeComputer($this->memory);
		
		$this->computer->setInputCallback([$this, 'setComputerInput']);
		$this->computer->setOutputCallback([$this, 'checkComputerOutput']);
	}
	
	
	public function start(): void
	{
		$this->memory->write(0, 2);
		$this->computer->run();
	}
	
	
	public function setComputerInput()
	{
		echo "\033[2J\033[H";
		echo $this->getPainting()."\n";
		echo "Score: {$this->score}\n";

		// Get the current x position of the ball
		$ballTiles = $this->getTilesById(Tile::ID_BALL);
		$ballX     = array_pop($ballTiles)->getX();
		
		// Get the current x position of the paddle
		$paddleTiles = $this->getTilesById(Tile::ID_HORIZONTALPADDLE);
		$paddleX     = array_pop($paddleTiles)->getX();
		
		if ($paddleX == $ballX) {
			$input = 0;
			
		} else {
			$input = $paddleX > $ballX ? -1 : 1;
		}
		
		// Let's wait for a bit
		usleep(100000);
		
		return $input;
	}
	
	
	public function checkComputerOutput($output): void
	{
		$this->computerOutput[] = $output;
		
		if (count($this->computerOutput) < 3) {
			return;
		}
		
		if ($this->computerOutput[0] == -1 && $this->computerOutput[1] == 0) {
			$this->score = $this->computerOutput[2];
			
		} else {
			$tile = new Tile($this->computerOutput[2]);
			$tile->setCoordinates($this->computerOutput[0], $this->computerOutput[1]);
			$this->tiles[] = $tile;
		}
		
		$this->computerOutput = [];
	}
	
	
	public function getPainting(): string
	{
		$tiles        = $this->groupTilesByCoordinates($this->getTiles());
		$minMaxCoords = $this->getMinMaxCoordinates();
		$painting     = '';
		
		for ($y = $minMaxCoords['minY']; $y <= $minMaxCoords['maxY']; $y++) {
			for ($x = $minMaxCoords['minX']; $x <= $minMaxCoords['maxX']; $x++) {
				$tileKey = "{$x},{$y}";
				$tile    = isset($tiles[$tileKey]) ? $tiles[$tileKey] : null;
				
				if ($tile === null) {
					$painting .= ' ';
					
				} else {
					$painting .= $tile->getChar();
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
		
		foreach ($this->getTiles() as $tile) {
			$tileX = $tile->getX();
			$tileY = $tile->getY();
			
			if ($tileX < $minMax['minX']) $minMax['minX'] = $tileX;
			if ($tileY < $minMax['minY']) $minMax['minY'] = $tileX;
			if ($tileX > $minMax['maxX']) $minMax['maxX'] = $tileX;
			if ($tileY > $minMax['maxY']) $minMax['maxY'] = $tileY;
		}
		
		return $minMax;
	}
	
	
	public function groupTilesByCoordinates($tiles)
	{
		$newTiles = [];
		
		foreach ($tiles as $tile) {
			$newTiles[$tile->getX().','.$tile->getY()] = $tile;
		}
		
		return $newTiles;
	}
	
	
	public function getTiles()
	{
		return $this->tiles;
	}
	
	
	public function getTilesById(int $id): array
	{
		$tiles = [];
		
		foreach ($this->getTiles() as $tile) {
			if ($tile->getId() == $id) {
				$tiles[] = $tile;
			}
		}
		
		return $tiles;
	}
	
	
	public function getScore(): int
	{
		return $this->score;
	}
}


class Tile
{
	const ID_EMPTY            = 0;
	const ID_WALL             = 1;
	const ID_BLOCK            = 2;
	const ID_HORIZONTALPADDLE = 3;
	const ID_BALL             = 4;
	
	private $tileId;
	private $x = 0;
	private $y = 0;
	
	
	public function __construct(int $tileId)
	{
		$this->tileId = $tileId;
	}
	
	
	public function getId(): int
	{
		return $this->tileId;
	}
	
	
	public function getX(): int
	{
		return $this->x;
	}
	
	
	public function getY(): int
	{
		return $this->y;
	}
	
	
	public function setCoordinates(int $x, int $y)
	{
		$this->x = $x;
		$this->y = $y;
	}
	
	
	public function getChar()
	{
		switch ($this->getId())
		{
			case self::ID_BLOCK:
				return "\e[47m \e[0m";
				
			case self::ID_BALL:
				return "0";
				
			case self::ID_HORIZONTALPADDLE:
				return "_";
				
			case self::ID_WALL:
				return "\e[45m \e[0m";
				
			default:
				return ' ';
		}
	}
}
