<?php

namespace IntComputer;

class Instruction
{
	private $memory;
	private $computer;
	private $finalAddress = 0;
	
	public function __construct(Memory $memory, IntComputer $computer)
	{
		$this->memory = $memory;
		$this->computer = $computer;
	}
	
	
	public function process($address)
	{
		$firstInstructionValue = $this->memory->read($address);
		$opcode                = Opcode::getOpcode($firstInstructionValue);
		
		$param1Value  = $this->getParameterValue($address + 1, Parameter::getMode($firstInstructionValue, 1));
		$param2Value  = $this->getParameterValue($address + 2, Parameter::getMode($firstInstructionValue, 2));
		
		switch ($opcode)
		{
			case Opcode::OPCODE_ADD:
				$this->finalAddress = $address + 4;
				$this->memory->write($this->memory->read($address + 3),
					$param1Value + $param2Value);
				break;
				
			case Opcode::OPCODE_MULTIPLY:
				$this->finalAddress = $address + 4;
				$this->memory->write($this->memory->read($address + 3),
					$param1Value * $param2Value);
				break;
				
			case Opcode::OPCODE_INPUT:
				$this->finalAddress = $address + 2;
				$this->memory->write($this->memory->read($address + 1),
					$this->computer->askInput("Please enter your input"));
				break;
				
			case Opcode::OPCODE_OUTPUT:
				$this->finalAddress = $address + 2;
				$this->computer->addOutput($param1Value);
				break;
				
			case Opcode::OPCODE_JUMPIFTRUE:
				$this->finalAddress = $param1Value != 0 ? $param2Value : $address + 3;
				break;
				
			case Opcode::OPCODE_JUMPIFFALSE:
				$this->finalAddress = $param1Value == 0 ? $param2Value : $address + 3;
				break;
				
			case Opcode::OPCODE_LESSTHAN:
				$this->finalAddress = $address + 4;
				$this->memory->write($this->memory->read($address + 3),
					$param1Value < $param2Value ? 1 : 0);
				break;
				
			case Opcode::OPCODE_EQUALS:
				$this->finalAddress = $address + 4;
				$this->memory->write($this->memory->read($address + 3),
					$param1Value == $param2Value ? 1 : 0);
				break;
				
			case Opcode::OPCODE_EXIT:
				$this->computer->exit();
				break;
		}
	}
	
	
	public function getFinalAddress(): int
	{
		return $this->finalAddress;
	}
	
	
	private function getParameterValue(int $address, int $mode)
	{
		switch ($mode)
		{
			case Parameter::MODE_LOCATION:
				return $this->memory->read($this->memory->read($address));
				break;
				
			case Parameter::MODE_IMMEDIATE:
				return $this->memory->read($address);
				break;
		}
		
		throw new Exception("Invalid parameter mode $mode");
	}
	
}
