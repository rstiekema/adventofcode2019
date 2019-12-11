<?php

namespace IntcodeComputer;

class Memory
{
	private $memory = [];
	
	
	public function __construct(array $memory)
	{
		$this->memory = $memory;
	}
	
	
	public function read($address):? int
	{
		return isset($this->memory[$address]) ? $this->memory[$address] : null;
	}
	
	
	public function write($address, int $value)
	{
//        echo "writing $value to address $address\n";
		$this->memory[$address] = $value;
	}
	
	
	public function __toString()
	{
		return join(',', $this->memory);
	}
	
}
