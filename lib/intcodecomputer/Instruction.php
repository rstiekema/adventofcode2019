<?php

namespace IntcodeComputer;

class Instruction
{
	private $memory;
	private $computer;
	private $finalAddress = 0;
	
	public function __construct(Memory $memory, IntcodeComputer $computer)
	{
		$this->memory = $memory;
		$this->computer = $computer;
	}
	
	
	public function process($address)
	{
		$firstInstructionValue = $this->memory->read($address);
		$paramValues           = [];
		$paramAddresses        = [];
		
		for ($i = 1; $i <= 3; $i++) {
			$paramAddress       = $this->getParameterValueAddress($i, $address, Parameter::getMode($firstInstructionValue, $i));
			$paramValues[$i]    = $this->memory->read($paramAddress);
			$paramAddresses[$i] = $paramAddress;
		}
		
		switch (Opcode::getOpcode($firstInstructionValue))
		{
			case Opcode::OPCODE_ADD:
				$this->memory->write($paramAddresses[3], $paramValues[1] + $paramValues[2]);
				$this->finalAddress = $address + 4;
				break;
				
			case Opcode::OPCODE_MULTIPLY:
				$this->memory->write($paramAddresses[3], $paramValues[1] * $paramValues[2]);
				$this->finalAddress = $address + 4;
				break;
				
			case Opcode::OPCODE_INPUT:
				$this->memory->write($paramAddresses[1], $this->computer->askInput("Please enter your input"));
				$this->finalAddress = $address + 2;
				break;
				
			case Opcode::OPCODE_OUTPUT:
				$this->computer->addOutput($paramValues[1]);
				$this->finalAddress = $address + 2;
				break;
				
			case Opcode::OPCODE_JUMPIFTRUE:
				$this->finalAddress = $paramValues[1] != 0 ? $paramValues[2] : $address + 3;
				break;
				
			case Opcode::OPCODE_JUMPIFFALSE:
				$this->finalAddress = $paramValues[1] == 0 ? $paramValues[2] : $address + 3;
				break;
				
			case Opcode::OPCODE_LESSTHAN:
				$this->memory->write($paramAddresses[3], $paramValues[1] < $paramValues[2] ? 1 : 0);
				$this->finalAddress = $address + 4;
				break;
				
			case Opcode::OPCODE_EQUALS:
				$this->memory->write($paramAddresses[3], $paramValues[1] == $paramValues[2] ? 1 : 0);
				$this->finalAddress = $address + 4;
				break;
				
			case Opcode::OPCODE_ADJUSTRELATIVEBASE:
				$this->computer->addRelativeBase($paramValues[1]);
				$this->finalAddress = $address + 2;
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
	
	
	private function getParameterValueAddress(int $parameter, int $instructionAddress, int $mode)
	{
		switch ($mode)
		{
			case Parameter::MODE_LOCATION:
				return $this->memory->read($instructionAddress + $parameter);
				
			case Parameter::MODE_IMMEDIATE:
				return $instructionAddress + $parameter;
				
			case Parameter::MODE_RELATIVE:
				return $this->computer->getRelativeBase() + $this->memory->read($instructionAddress + $parameter);
		}
		
		throw new Exception("Invalid parameter mode $mode");
	}
	
}
