<?php

for ($i = 1; $i <= 100; $i++) {
	if ($i % 3 == 0) {
        if ($i % 5 == 0) {
            echo 'foobar';
        } else {
            echo 'foo';
        }
	} elseif ($i % 5 == 0) {
        echo 'bar';
    } else {
        echo $i;
    }
    if ($i < 100) {
        echo ', ';
    }
}
echo "\n";
