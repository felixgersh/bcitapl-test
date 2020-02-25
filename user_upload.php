<?php

require_once 'CommandLineParser.php';

try {
    $parser = new CommandLineParser($argc, $argv);
    $parser->printOptions();
} catch (Exception $e) {
    if ($e->getCode() == -1) {
        CommandLineParser::displayHelp();
    } else {
        echo "\nError: ".$e->getMessage()."\n";
        exit(1);
    }
}
