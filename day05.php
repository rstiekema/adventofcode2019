<?php

$input    = "1,1,1,4,99,5,6,0,99";
$expected = "30,1,1,4,2,5,6,0,99";

$input    = "1002,4,3,4,33";
$expected = "1002,4,3,4,99";

$input    = "1101,100,-1,4,0";
$expected = "1101,100,-1,4,99";


$input = file_get_contents('resources/day05.txt');


$program  = new Program(explode(",", $input));
$computer = new IntComputer($program);
$computer->run();


echo "Program output: ".$computer->getOutput()."\n";
//echo "\n";
//echo "Program result:  $program\n";
//echo "Expected result: $expected\n";



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
            $pointer += $instruction->getInstructionLength();
            
        } while ($this->program->getLocationValue($pointer) && !$this->stopped);
    }
    
    
    public function askInput(string $text): int
    {
        do {
            echo $text.": ";
            $input = trim(fgets(STDIN));
            
        } while (empty($input));
        
        return $input;
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
    
    
    public function getLocationValue($location):? int
    {
        return isset($this->program[$location]) ? $this->program[$location] : null;
    }
    
    
    public function writeLocationValue($location, int $value)
    {
        echo "writing $value to location $location\n";
        $this->program[$location] = $value;
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
    private $instructionLength = 4;
    
    
    public function __construct(Program $program, IntComputer $computer)
    {
        $this->program = $program;
        $this->computer = $computer;
    }
    
    
    public function process($location)
    {
        $firstInstructionValue = $this->program->getLocationValue($location);
        $opcode                = Opcode::getOpcode($firstInstructionValue);
        
        $param1Value  = $this->getParameterValue($location + 1, Parameter::getMode($firstInstructionValue, 1));
        $param2Value  = $this->getParameterValue($location + 2, Parameter::getMode($firstInstructionValue, 2));
        
        switch ($opcode)
        {
            case Opcode::OPCODE_ADD:
                echo "adding values\n";
                $this->program->writeLocationValue($this->program->getLocationValue($location + 3),
                    $param1Value + $param2Value);
                break;
                
            case Opcode::OPCODE_MULTIPLY:
                echo "multiplying values\n";
                $this->program->writeLocationValue($this->program->getLocationValue($location + 3),
                    $param1Value * $param2Value);
                break;
                
            case Opcode::OPCODE_INPUT:
                echo "asking for input\n";
                $this->instructionLength = 2;
                $this->program->writeLocationValue($this->program->getLocationValue($location + 1),
                    $this->computer->askInput("Please enter your input"));
                break;
                
            case Opcode::OPCODE_OUTPUT:
                echo "adding output $param1Value\n";
                $this->instructionLength = 2;
                $this->computer->addOutput($param1Value);
                break;
                
            case Opcode::OPCODE_EXIT:
                echo "exiting\n";
                $this->computer->exit();
                break;
        }
    }
    
    
    public function getInstructionLength()
    {
        return $this->instructionLength;
    }
    
    
    private function getParameterValue(int $location, int $mode)
    {
        switch ($mode)
        {
            case Parameter::MODE_LOCATION:
                return $this->program->getLocationValue($this->program->getLocationValue($location));
                break;
                
            case Parameter::MODE_IMMEDIATE:
                return $this->program->getLocationValue($location);
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
    const OPCODE_ADD      = 1;
    const OPCODE_MULTIPLY = 2;
    const OPCODE_INPUT    = 3;
    const OPCODE_OUTPUT   = 4;
    const OPCODE_EXIT     = 99;
    
    
    public static function getOpcode(int $firstInstructionValue)
    {
        return (int)substr($firstInstructionValue, -2);
    }
    
    
}