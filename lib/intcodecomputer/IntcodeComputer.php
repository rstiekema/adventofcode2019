<?php

namespace IntcodeComputer;

class IntcodeComputer
{
	/**
	 * @var Memory
	 */
	private $program;
	
	/**
	 * @var array
	 */
	private $output = [];
	
	/**
     * Has the program stopped?
     * @var bool
     */
	private $stopped = false;
	
	
	public function __construct(Memory $memory)
	{
		$this->program = $memory;
	}
	
	
	public function run()
	{
		$pointer = 0;
		
		do {
			$instruction = new Instruction($this->program, $this);
			$instruction->process($pointer);
			$pointer = $instruction->getFinalAddress();
			
		} while ($this->program->read($pointer) !== null && !$this->stopped);
	}
	
	
	public function askInput(string $text): int
	{
		do {
			echo $text.': ';
			$input = trim(fgets(STDIN));
			
		} while ($input === '');
		
		return (int)$input;
	}
	
	
	public function addOutput(int $output): void
	{
		$this->output[] = $output;
	}
	
	
	public function exit()
	{
		$this->stopped = true;
	}
	
	
	public function getOutput()
	{
		return join(',', $this->output);
	}
	
}
