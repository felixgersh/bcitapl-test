<?php

require_once 'CommandLineParser.php';
require_once 'DatabaseEngine.php';
require_once 'CSVReaderWriter.php';

$db = null;
try {
    $parser = new CommandLineParser($argc, $argv);
    $parser->printOptions();
    $options = $parser->getOptions();
    if (!isset($options['--dry_run'])) {
        $db = new DatabaseEngine($options);
        echo "Connected to the DB\n";
    }
    if (isset($options['--create_table'])) {
        if ($db->getTableExistence()) {
            $recordCount = $db->getRecordsCount();
            $str = readline("Database table 'users' already exists and contains $recordCount records, do you want to recreate it? [YN] ");
            $str = strtoupper($str);
            if (in_array($str, ['Y', 'YES'])) {
                $db->dropTable();
                echo "Table 'users' has been dropped.\n";
            } else {
                echo "Exiting without recreation.\n";
                $db->close();
                exit(0);
            }
            //echo "$str\n";
        }
        $db->createTable();
        echo "Table 'users' has been created.\n";
    } else { // --file
        $csv = new CSVReaderWriter($db, $options['--file'], isset($options['--dry_run']));
        $csv->readFileWriteDb();
    }
    echo "\nFinished successfully.\n\n";
    if ($db !== null) {
        $db->close();
    }
} catch (Exception $e) {
    if ($e->getCode() == -1) {
        CommandLineParser::displayHelp();
    } else {
        echo "\nError: ".$e->getMessage()."\n\n";
        //echo $db === null ? 'null' : 'not null';
        //echo "\n";
        if ($db !== null) {
            $db->close();
        }
        exit(1);
    }
}
