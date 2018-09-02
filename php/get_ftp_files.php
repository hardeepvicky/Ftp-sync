<?php
$csv = new CsvUtility(LOG_FTP_FILE);

$data = $csv->fetch();

echo json_encode($data); exit;

