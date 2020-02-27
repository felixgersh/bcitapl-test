<?php

define('CREATE_TABLE_OPTION', '--create_table');
define('DRY_RUN_OPTION', '--dry_run');
define('CSV_FILE_OPTION', '--file');
define('HELP_OPTION', '--help');

class CommandLineParser
{
    private $options = [];
    private $shortOptions = [
        '-h' => 'MySQL host',
        '-u' => 'MySQL username',
        '-p' => 'MySQL password'
    ];
    private $argc, $argv;

    public function __construct($argc, $argv)
    {
        $this->argc = $argc;
        $this->argv = $argv;
        for ($i = 1; $i < $argc; $i++) {
            if ($argv[$i] == CSV_FILE_OPTION) {
                if ($i + 1 == $argc) {
                    throw new Exception('CSV filename should be specified');
                }
                $filename = $argv[++$i];
                if ((strlen($filename) < 1) || ($filename[0] == '-')) {
                    throw new Exception('CSV filename should not be empty and should not start with -');
                }
                $this->options[CSV_FILE_OPTION] = $filename;
            } elseif ($argv[$i] == CREATE_TABLE_OPTION) {
                $this->options[CREATE_TABLE_OPTION] = true;
            } elseif ($argv[$i] == DRY_RUN_OPTION) {
                $this->options[DRY_RUN_OPTION] = true;
            } elseif ($argv[$i] == HELP_OPTION) {
                throw new Exception('', -1);
            } elseif ($this->assignShortOption($i, '-h')) {
            } elseif ($this->assignShortOption($i, '-u')) {
            } elseif ($this->assignShortOption($i, '-p')) {
            } else {
                throw new Exception('unknown option in the command line: '.$argv[$i].', run the script with '.HELP_OPTION.' option');
            }
        }
        if (isset($this->options[CREATE_TABLE_OPTION])) {
            if (isset($this->options[DRY_RUN_OPTION])) {
                throw new Exception('you cannot use '.CREATE_TABLE_OPTION.' in '.DRY_RUN_OPTION.' mode');
            }
            if (isset($this->options[CSV_FILE_OPTION])) {
                throw new Exception('you cannot use '.CREATE_TABLE_OPTION.' together with CSV file upload');
            }
            $this->checkShortOptions();
        } elseif (!isset($this->options[CSV_FILE_OPTION])) {
            throw new Exception('either '.CSV_FILE_OPTION.' or '.CREATE_TABLE_OPTION.' should be specified');
        } elseif (!isset($this->options[DRY_RUN_OPTION])) {
            $this->checkShortOptions();
        }
    }

    private function assignShortOption(&$i, $optionName)
    {
        if ($this->argv[$i] == $optionName) {
            $optionMeaning = $this->shortOptions[$optionName];
            if ($i + 1 == $this->argc) {
                throw new Exception("$optionMeaning should be specified");
            }
            $paramValue = $this->argv[++$i];
            if ($paramValue[0] == '-') {
                throw new Exception("If your $optionMeaning should start with '-', specify it without space after $optionName");
            }
            $this->options[$optionName] = $paramValue;
            return true;
        }
        if (strpos($this->argv[$i], $optionName) === 0) {
            $paramValue = substr($this->argv[$i], strlen($optionName));
            $this->options[$optionName] = $paramValue;
            return true;
        }
        return false;
    }

    private function checkShortOptions()
    {
        foreach ($this->shortOptions as $optionName => $optionMeaning) {
            if (!isset($this->options[$optionName])) {
                throw new Exception($optionMeaning.' should be specified');
            }
        }
    }

    public static function displayHelp($scriptName)
    {
        $helpMessage = <<<HELP
Usage: php {$scriptName} [OPTIONS]

The options are:
--file [csv file name] - this is the name of the CSV file to be parsed
--dry_run - this can be used with the --file directive in case you want to run
            the script, but not insert into the DB. All other functions
            will be executed, but the database will not be altered
--create_table - this will cause the MySQL users table to be built
                 (and no further action will be taken)
-u - MySQL username
-p - MySQL password
-h - MySQL host
--help - this will output the above list of directives
HELP;
        echo $helpMessage."\n\n";
    }

    public function printOptions()
    {
        print_r($this->options);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function isCreateTableMode()
    {
        return isset($this->options[CREATE_TABLE_OPTION]);
    }

    public function isDryRunMode()
    {
        return isset($this->options[DRY_RUN_OPTION]);
    }

    public function getCSVFilePath()
    {
        return isset($this->options[CSV_FILE_OPTION]) ? $this->options[CSV_FILE_OPTION] : '';
    }

}
