<?php

$output = fopen('php://memory', 'w');

// output the column headings
fputcsv($output, $intestazioni);

// loop over the rows, outputting them
foreach ($rows as $row)
   fputcsv($output, $row, ";");
// reset the file pointer to the start of the file
fseek($output, 0);
// tell the browser it's going to be a csv file
header('Content-Type: application/csv');
// tell the browser we want to save it instead of displaying it
header('Content-Disposition: attachment; filename="' . $filename . '";');
// make php send the generated csv lines to the browser
fpassthru($output);
?>