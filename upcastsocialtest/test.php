<?php

require_once 'lib/helper.class.php';
require_once 'lib/model.class.php';
require_once 'lib/writer.class.php';

$nextMonthCount = 6;

$columns = array('Month', 'Mid Month Meeting Date', 'End of Month Testing Date');
$rows = model::getSchedule($nextMonthCount);

try {
$filename = csvWriter::generate($columns, $rows);

print "Csv file generated path: $filename\n";
}
catch (Exception $e) {
print "Exception: $e->getMessage()";
}
