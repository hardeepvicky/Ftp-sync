<?php
$csv = new CsvUtility(LOG_CHANGE_FILE);

$data = $csv->fetch();

echo json_encode($data); exit;

