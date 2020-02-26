<?php

define('BUFFER_LEN', 4); // for a real usage would be good to set to 500 or 1000

class CSVReaderWriter
{

    private $handle = null;
    private $dryRun;

    public function __construct($filename, $dryRun)
    {
        $this->handle = @fopen($filename, 'r');
        if ($this->handle === false) {
            throw new Exception("could not open file $filename");
        }
        $this->dryRun = $dryRun;
    }

    public function __destruct()
    {
        if ($this->handle !== null) {
            fclose($this->handle);
        }
    }

    private function validateCSVLine($counter, &$data)
    {
        if (count($data) != 3) {
            throw new Exception("line $counter: CSV line should contain three values: name, surname and email");
        }
        foreach (['name', 'surname', 'email'] as $key => $param) {
            $data[$key] = trim($data[$key]);
            if (strlen($data[$key]) == 0) {
                throw new Exception("line $counter: $param should not be empty");
            }
        }
        if (!filter_var($data[2], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("line $counter: email '{$data[2]}' is invalid");
        }
    }

    public function readFileWriteDb()
    {
        $totalCounter = 0;
        $bufferCounter = 0;
        $buffer = [];
        fgetcsv($this->handle); // skipping first line, as it is header
        while (($data = fgetcsv($this->handle)) !== false) {
            $totalCounter++;
            $bufferCounter++;
            $this->validateCSVLine($totalCounter, $data);
            $buffer[] = $data;
            if ($bufferCounter == BUFFER_LEN) {
                // write buffer to the DB
                $bufferCounter = 0;
                $buffer = [];
            }
        }
    }

}
