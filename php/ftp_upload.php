<?php
ini_set("max_execution_time", 60 * 60 * 3);

$file_csv = $_POST['filename'];

$change_data = CsvUtility::fetchCSV(LOG_CHANGE_FILE);

foreach($change_data as $i => $record)
{
    $change_data[$i]['is_done'] = 0;
}

CsvUtility::writeCSV($file_csv, $change_data, true);

header('Cache-Control: no-cache');
try
{
    $ftp = new FtpUtility(FTP_SERVER, FTP_USER, FTP_PASSWORD);
    
    foreach($change_data as $i => $record)
    {
        $ftp_file = FTP_PROJECT_PATH . $record['name'];
        if ($record['type'] == "D")
        {
            if ($ftp->delete($ftp_file))
            {
                $change_data[$i]['is_done'] = 1;
            }
        }
        else
        {
            if ($ftp->upload($record['file'], $ftp_file))
            {
                $change_data[$i]['is_done'] = 1;
            }
        }
        
        CsvUtility::writeCSV($file_csv, $change_data, true);
    }
}
catch (Exception $ex)
{
    header('HTTP/1.1 500 Internal Server Error');
    echo $ex->getMessage();
}

echo 1; exit;