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
		
		echo "address $address [$firstInstructionValue]: ";
		
		switch (Opcode::getOpcode($firstInstructionValue))
		{
			case Opcode::OPCODE_ADD:
				$writeAddress = $this->memory->read($address + 3);
				echo "writing [{$paramAddresses[1]}]{$paramValues[1]} + [{$paramAddresses[2]}]{$paramValues[2]} to address $writeAddress\n";
				$this->finalAddress = $address + 4;
				$this->memory->write($writeAddress, $paramValues[1] + $paramValues[2]);
				break;
				
			case Opcode::OPCODE_MULTIPLY:
				$writeAddress = $this->memory->read($address + 3);
				echo "writing [{$paramAddresses[1]}]{$paramValues[1]} * [{$paramAddresses[2]}]{$paramValues[2]} to address $writeAddress\n";
				$this->finalAddress = $address + 4;
				$this->memory->write($writeAddress, $paramValues[1] * $paramValues[2]);
				break;
				
			case Opcode::OPCODE_INPUT:
				$writeAddress = $this->memory->read($address + 1);
				echo "writing input to address $writeAddress\n";
				$this->finalAddress = $address + 2;
				$this->memory->write($writeAddress, $this->computer->askInput("Please enter your input"));
				break;
				
			case Opcode::OPCODE_OUTPUT:
				echo "adding output [{$paramAddresses[1]}]{$paramValues[1]}\n";
				$this->finalAddress = $address + 2;
				$this->computer->addOutput($paramValues[1]);
				break;
				
			case Opcode::OPCODE_JUMPIFTRUE:
				echo "jumping to [{$paramAddresses[2]}]{$paramValues[2]} if [{$paramAddresses[1]}]{$paramValues[1]} != 0\n";
				$this->finalAddress = $paramValues[1] != 0 ? $paramValues[2] : $address + 3;
				break;
				
			case Opcode::OPCODE_JUMPIFFALSE:
				echo "jumping to [{$paramAddresses[2]}]{$paramValues[2]} if [{$paramAddresses[1]}]{$paramValues[1]} == 0\n";
				$this->finalAddress = $paramValues[1] == 0 ? $paramValues[2] : $address + 3;
				break;
				
			case Opcode::OPCODE_LESSTHAN:
				$writeAddress = $this->memory->read($address + 3);
				echo "writing 1 if [{$paramAddresses[1]}]{$paramValues[1]} < [{$paramAddresses[2]}]{$paramValues[2]}, else 0 to address $writeAddress\n";
				$this->finalAddress = $address + 4;
				$this->memory->write($writeAddress, $paramValues[1] < $paramValues[2] ? 1 : 0);
				break;
				
			case Opcode::OPCODE_EQUALS:
				$writeAddress = $this->memory->read($address + 3);
				echo "writing 1 if [{$paramAddresses[1]}]{$paramValues[1]} = [{$paramAddresses[2]}]{$paramValues[2]}, else 0 to address $writeAddress\n";
				$this->finalAddress = $address + 4;
				$this->memory->write($writeAddress, $paramValues[1] == $paramValues[2] ? 1 : 0);
				break;
				
			case Opcode::ADJUST_RELATIVEBASE:
				echo "adjust relative base by [{$paramAddresses[1]}]{$paramValues[1]}\n";
				$this->finalAddress = $address + 2;
				$this->computer->addRelativeBase($paramValues[1]);
				break;
				
			case Opcode::OPCODE_EXIT:
				echo "exit\n";
				$this->computer->exit();
				break;
		}
		
//		echo "current memory state: ".substr($this->memory, 0, 150)."...\n";
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
