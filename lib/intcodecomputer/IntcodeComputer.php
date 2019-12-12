<?php

namespace IntcodeComputer;

class IntcodeComputer
{
	/**
	 * @var Memory
	 */
	private $memory;
	
	/**
	 * @var array
	 */
	private $output = [];
	private $lastOutput = 0;
	
	/**
	 * @var array
	 */
	private $input = [];
	
	public $started = false;
	private $paused = false;
	private $stopped = false;
	
	/**
	* @var callable
	 */
	private $outputCallback;
	
	/**
	 * @var string|int
	 */
	public $identifier;
	
	
	private $currentPointer = 0;
	private $relativeBase = 0;
	
	
	public function __construct(Memory $memory)
	{
		$this->memory = $memory;
	}
	
	
	public function run()
	{
		$this->paused = false;
		$this->started = true;
		
		do {
			$instruction = new Instruction($this->memory, $this);
			$instruction->process($this->currentPointer);
			$this->currentPointer = $instruction->getFinalAddress();
			
			if (!empty($this->output) && $this->outputCallback !== null) {
				$output = array_shift($this->output);
				call_user_func($this->outputCallback, $output);
			}
			
		} while ($this->memory->read($this->currentPointer) !== null && !$this->stopped && !$this->paused);
	}
	
	
	public function pause()
	{
		$this->started = false;
		$this->paused = true;
	}
	
	
	public function exit()
	{
		$this->stopped = true;
	}
	
	
	public function addInput($input): void
	{
		$this->input[] = $input;
	}
	
	
	public function askInput(string $text): int
	{
		$input = array_shift($this->input);
		
		if ($input === null) {
			do {
				echo $text.': ';
				$input = trim(fgets(STDIN));
				
			} while ($input === '');
		}
		
		return (int)$input;
	}
	
	
	public function addOutput(int $output): void
	{
		$this->output[] = $output;
		$this->lastOutput = $output;
	}
	
	
	public function setOutputCallback(callable $function): void
	{
		$this->outputCallback = $function;
	}
	
	
	public function addRelativeBase(int $positions): void
	{
		$this->relativeBase += $positions;
	}
	
	
	public function getRelativeBase(): int
	{
		return $this->relativeBase;
	}
	
	
	public function getOutput()
	{
		return !empty($this->output) ? join(',', $this->output) : $this->lastOutput;
	}
	
}
