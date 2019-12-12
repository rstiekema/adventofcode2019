<?php

namespace IntcodeComputer;

class Memory
{
	private $memory = [];
	
	
	public function __construct(array $memory)
	{
		$this->memory = $memory;
	}
	
	
	public function read($address): int
	{
		return isset($this->memory[$address]) ? $this->memory[$address] : 0;
	}
	
	
	public function write($address, int $value)
	{
		$this->memory[$address] = $value;
	}
	
	
	public function __toString()
	{
		return join(',', $this->memory);
	}
	
}
