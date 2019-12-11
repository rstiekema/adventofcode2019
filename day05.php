<?php

$input    = "1,1,1,4,99,5,6,0,99";
$expected = "30,1,1,4,2,5,6,0,99";

$input    = "1002,4,3,4,33";
$expected = "1002,4,3,4,99";

$input    = "1101,100,-1,4,0";
$expected = "1101,100,-1,4,99";

$input    = "3,9,8,9,10,9,4,9,99,-1,8"; // position mode, equal to 8 = output 1
$input    = "3,9,7,9,10,9,4,9,99,-1,8"; // position mode, less than 8 = output 1
$input    = "3,3,1108,-1,8,3,4,3,99"; // immediate mode, equal to 8 = output 1
$input    = "3,3,1107,-1,8,3,4,3,99"; // immediate mode, less than 8 = output 1
$input    = "3,12,6,12,15,1,13,14,13,4,13,99,-1,0,1,9"; // position mode, jump test, output 0 if input 0, else output 1
$input    = "3,3,1105,-1,9,1101,0,0,12,4,12,99,1"; // immediate mode, jump test, output 0 if input 0, else output 1
$input    = "3,21,1008,21,8,20,1005,20,22,107,8,21,20,1006,20,31,1106,0,36,98,0,0,1002,21,125,20,4,20,1105,1,46,104,999,1105,1,46,1101,1000,1,20,4,20,1105,1,46,98,99"; // output 999 if input below 8, output 1000 if input equal to 8, output 1001 if input greater than 8


$input = file_get_contents('resources/day05.txt');


$program  = new Program(explode(",", $input));
$computer = new IntComputer($program);
$computer->run();


echo "Program output: ".$computer->getOutput()."\n";



class IntComputer
{
    /**
     * @var Program
     */
    private $program;
    
    /**
     * @var array
     */
    private $output = [];
    
    private $stopped = false;
    
    
    public function __construct(Program $program)
    {
        $this->program = $program;
    }
    
    
    public function run()
    {
        $pointer = 0;
        
        do {
            $instruction = new Instruction($this->program, $this);
            $instruction->process($pointer);
            $pointer = $instruction->getFinalAddress();
            
        } while ($this->program->getValue($pointer) !== null && !$this->stopped);
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


class Program
{
    private $program = [];
    
    
    public function __construct(array $program)
    {
        $this->program = $program;
    }
    
    
    public function getValue($address):? int
    {
        return isset($this->program[$address]) ? $this->program[$address] : null;
    }
    
    
    public function writeValue($address, int $value)
    {
//        echo "writing $value to address $address\n";
        $this->program[$address] = $value;
    }
    
    
    public function __toString()
    {
        return join(',', $this->program);
    }
    
}


class Instruction
{
    private $program;
    private $computer;
    private $finalAddress = 0;
    
    public function __construct(Program $program, IntComputer $computer)
    {
        $this->program = $program;
        $this->computer = $computer;
    }
    
    
    public function process($address)
    {
        $firstInstructionValue = $this->program->getValue($address);
        $opcode                = Opcode::getOpcode($firstInstructionValue);
        
        $param1Value  = $this->getParameterValue($address + 1, Parameter::getMode($firstInstructionValue, 1));
        $param2Value  = $this->getParameterValue($address + 2, Parameter::getMode($firstInstructionValue, 2));
        
        switch ($opcode)
        {
            case Opcode::OPCODE_ADD:
                $this->finalAddress = $address + 4;
                $this->program->writeValue($this->program->getValue($address + 3),
                    $param1Value + $param2Value);
                break;
                
            case Opcode::OPCODE_MULTIPLY:
                $this->finalAddress = $address + 4;
                $this->program->writeValue($this->program->getValue($address + 3),
                    $param1Value * $param2Value);
                break;
                
            case Opcode::OPCODE_INPUT:
                $this->finalAddress = $address + 2;
                $this->program->writeValue($this->program->getValue($address + 1),
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
				$this->program->writeValue($this->program->getValue($address + 3),
                    $param1Value < $param2Value ? 1 : 0);
				break;
				
			case Opcode::OPCODE_EQUALS:
        		$this->finalAddress = $address + 4;
				$this->program->writeValue($this->program->getValue($address + 3),
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
                return $this->program->getValue($this->program->getValue($address));
                break;
                
            case Parameter::MODE_IMMEDIATE:
                return $this->program->getValue($address);
                break;
        }
        
        throw new Exception("Invalid parameter mode $mode");
    }
    
}



class Parameter
{
    const MODE_LOCATION  = 0;
    const MODE_IMMEDIATE = 1;
    
    
    public static function getMode(int $firstInstructionValue, int $parameter)
    {
        if (strlen($firstInstructionValue) < 2 + $parameter) {
            return self::MODE_LOCATION;
        }
        
        return (int)substr($firstInstructionValue, -2 - $parameter, 1);
    }
    
}



class Opcode
{
    const OPCODE_ADD         = 1;
    const OPCODE_MULTIPLY    = 2;
    const OPCODE_INPUT       = 3;
    const OPCODE_OUTPUT      = 4;
    const OPCODE_JUMPIFTRUE  = 5;
    const OPCODE_JUMPIFFALSE = 6;
    const OPCODE_LESSTHAN    = 7;
    const OPCODE_EQUALS      = 8;
    const OPCODE_EXIT        = 99;
    
    
    public static function getOpcode(int $firstInstructionValue)
    {
        return (int)substr($firstInstructionValue, -2);
    }
    
}