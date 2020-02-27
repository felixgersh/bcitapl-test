<?php

define('BUFFER_LEN', 4); // for a real usage would be good to set to 500 or 1000

class CSVReaderWriter
{
    private $db;
    private $handle = null;
    private $dryRun, $processMessage;

    public function __construct($db, $filename, $dryRun)
    {
        $this->db = $db;
        $this->handle = @fopen($filename, 'r');
        if ($this->handle === false) {
            throw new Exception("could not open file $filename");
        }
        $this->dryRun = $dryRun;
        $this->processMessage = $dryRun ? 'processed' : 'inserted';
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
            if ($param == 'name') {
                $data[$key] = ucfirst(strtolower($data[$key]));
            } elseif ($param == 'surname') {
                $surname = $data[$key];
                if (($surname[1] == "'") && (($surname[0] == 'O') || ($surname[0] == 'o'))) {
                    $surname = "O'".ucfirst(strtolower(substr($surname, 2)));
                } else {
                    $surname = ucfirst(strtolower($surname));
                }
                $data[$key] = $surname;
            } else { // email
                if (!filter_var($data[$key], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("line $counter: $param '{$data[$key]}' is invalid");
                }
                $data[$key] = strtolower($data[$key]);
            }
        }
    }

    private function writeBufferToDb(&$buffer, &$bufferCounter, $totalCounter)
    {
        if (!$this->dryRun) {
            $sql = 'INSERT INTO `users` (`Name`, `Surname`, `Email`) VALUES ';
            $conn = $this->db->getConnection();
            $nextRecord = false;
            foreach ($buffer as $record) {
                if ($nextRecord) {
                    $sql .= ',';
                } else {
                    $nextRecord = true;
                }
                $name = $conn->real_escape_string($record[0]);
                $surname = $conn->real_escape_string($record[1]);
                $email = $conn->real_escape_string($record[2]);

                $sql .= "('$name', '$surname', '$email')";
            }
            if (!$conn->query($sql)) {
                throw new Exception($conn->error);
            }
        }
        $startRecordNum = $totalCounter - $bufferCounter + 1;
        echo "Records from $startRecordNum to $totalCounter {$this->processMessage}\n";
        $buffer = [];
        $bufferCounter = 0;
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
                $this->writeBufferToDb($buffer, $bufferCounter, $totalCounter);
            }
        }
        if (!empty($buffer)) {
            $this->writeBufferToDb($buffer, $bufferCounter, $totalCounter);
        }
    }

}
