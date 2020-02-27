<?php

require_once 'CommandLineParser.php';
require_once 'DatabaseEngine.php';
require_once 'CSVReaderWriter.php';

$db = null;
try {
    $parser = new CommandLineParser($argc, $argv);
    //$parser->printOptions(); // was used here for debug purposes
    if (!$parser->isDryRunMode()) {
        $db = new DatabaseEngine($parser->getOptions());
        echo "Connected to the DB\n";
    }
    if ($parser->isCreateTableMode()) {
        if ($db->getTableExistence()) {
            $recordCount = $db->getRecordsCount();
            $str = readline(sprintf("Database table '%s' already exists and contains %s records, do you want to recreate it? [YN] ", TABLE_NAME, $recordCount));
            if (in_array(strtoupper($str), ['Y', 'YES'])) {
                $db->dropTable();
                printf("Table '%s' has been dropped.\n", TABLE_NAME);
            } else {
                echo "Exiting without recreation.\n\n";
                $db->close();
                exit(0);
            }
        }
        $db->createTable();
        printf("Table '%s' has been created.\n", TABLE_NAME);
    } else {
        $csv = new CSVReaderWriter($db, $parser->getCSVFilePath(), $parser->isDryRunMode());
        $csv->readFileWriteDb();
    }
    echo "\nFinished successfully.\n\n";
    if ($db !== null) {
        $db->close();
    }
} catch (Exception $e) {
    if ($e->getCode() == -1) {
        CommandLineParser::displayHelp($argv[0]);
    } else {
        echo "\nError: ".$e->getMessage()."\n\n";
        if ($db !== null) {
            $db->close();
        }
        exit(1);
    }
}
