<?php
ini_set("max_execution_time", 60 * 60 * 3);

header('Cache-Control: no-cache');

function write_log(FtpUtility $ftp, CsvUtility $csv, $ftp_path, $base_ftp_path = "")
{    
    $files = ftp_nlist($ftp->conn, $ftp_path);

    $ftp_files = array();

    $p = str_replace($base_ftp_path, "", $ftp_path);
    foreach($files as $k => $file)
    {
        if ($file == '.' || $file == '..') 
        {
        }
        else if ($ftp->isDir($ftp_path . $file))
        {
            write_log($ftp, $csv, $ftp_path . $file . "/", $base_ftp_path);                    
        }
        else
        {
            $file_arr = array(
                "name" => $p . $file,
                "file" => $ftp_path . $file,
                "size" => ftp_size($ftp->conn, $ftp_path . $file)
            );

            $ftp_files[$p . $file] = $file_arr;
        }
    }
    
    $data = array();
    foreach($ftp_files as $file => $file_arr)
    {
        $data[] = $file_arr;
    }
    
    $csv->write($data, false, ",", "a");
}

$ftp = new FtpUtility(FTP_SERVER, FTP_USER, FTP_PASSWORD);

$csv = new CsvUtility(LOG_FTP_FILE);
$csv->write(array("header" => array("name","file", "size")));
write_log($ftp, $csv, FTP_PROJECT_PATH, FTP_PROJECT_PATH);

echo 1; exit;