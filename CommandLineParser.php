<?php

class CommandLineParser
{
    private $options = [];
    private $shortOptions = [
        ['-h', 'MySQL host'],
        ['-u', 'MySQL username'],
        ['-p', 'MySQL password']
    ];
    private $argc, $argv;

    public function __construct($argc, $argv)
    {
        $this->argc = $argc;
        $this->argv = $argv;
        //print_r([$argc, $argv]);
        for ($i = 1; $i < $argc; $i++) {
            //echo $argv[$i]."\n";
            if ($argv[$i] == '--file') {
                if ($i + 1 == $argc) {
                    throw new Exception('CSV filename should be specified');
                }
                $filename = $argv[++$i];
                if ((strlen($filename) < 1) || ($filename[0] == '-')) {
                    throw new Exception('CSV filename should not be empty and should not start with -');
                }
                $this->options['--file'] = $filename;
            } elseif ($argv[$i] == '--create_table') {
                $this->options['--create_table'] = true;
            } elseif ($argv[$i] == '--dry_run') {
                $this->options['--dry_run'] = true;
            } elseif ($argv[$i] == '--help') {
                throw new Exception('', -1);
            } elseif ($this->assignShortOption($i, '-h', 'MySQL host')) {
            } elseif ($this->assignShortOption($i, '-u', 'MySQL username')) {
            } elseif ($this->assignShortOption($i, '-p', 'MySQL password')) {
            } else {
                throw new Exception('unknown option in the command line: '.$argv[$i]);
            }
        }
        if (isset($this->options['--create_table'])) {
            if (isset($this->options['--dry_run'])) {
                throw new Exception('you cannot run --create_table in --dry_run mode');
            }
            if (isset($this->options['--file'])) {
                throw new Exception('you cannot run --create_table together with CSV file upload');
            }
            $this->checkShortOptions();
        } elseif (!isset($this->options['--file'])) {
            throw new Exception('either --file or --create_table should be specified');
        }
    }

    private function assignShortOption(&$i, $optionName, $optionMeaning)
    {
        if ($this->argv[$i] == $optionName) {
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
        foreach ($this->shortOptions as $option) {
            if (!isset($this->options[$option[0]])) {
                throw new Exception($option[1].' should be specified');
            }
        }
    }

    public static function displayHelp()
    {
        $helpMessage = <<<HELP
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
        echo "\n".$helpMessage."\n";
    }

    public function printOptions()
    {
        print_r($this->options);
    }

    public function getOptions()
    {
        return $this->options;
    }

}
