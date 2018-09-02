<?php
ini_set("max_execution_time", 60 * 60 * 3);

header('Cache-Control: no-cache');

function write_change_log($local_path, FtpUtility $ftp, CsvUtility $csv, $ftp_path, $base_local_path = "", $base_ftp_path = "")
{    
    $files = scandir($local_path);
       
    $local_files = array();
    
    $p = str_replace($base_local_path, "", $local_path);
    foreach($files as $k => $file)
    {
         if ($file == '.' || $file == '..') 
         {
         }
         else if (is_dir($local_path . $file))
         {
            write_change_log($local_path . $file . "/", $ftp, $csv, $ftp_path . $file . "/", $base_local_path, $base_ftp_path);
         }
         else
         {
            $file_arr = array(
                "name" => $p . $file,
                "file" => $local_path . $file,
                "size" => filesize($local_path . $file)
            );
            
            $local_files[$p . $file] = $file_arr;
         }
    }    
    
    $files = ftp_nlist($ftp->conn, $ftp_path);

    $ftp_files = array();

    $p = str_replace($base_ftp_path, "", $ftp_path);
    foreach($files as $k => $file)
    {
        if ($file == '.' || $file == '..' || $ftp->isDir($ftp_path . $file)) 
        {
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
    foreach($local_files as $file => $file_arr)
    {
        if (isset($ftp_files[$file]))
        {
            $ftp_file_arr = $ftp_files[$file];
            if ($file_arr['size'] != $ftp_file_arr['size'])
            {
                $file_arr["type"] = "C";
                $data[] = $file_arr;
            }
        }
        else
        {
            $file_arr["type"] = "A";
            $data[] = $file_arr;
        }
    }
    
    foreach($ftp_files as $ftp_file => $ftp_file_arr)
    {
        if (!isset($local_files[$ftp_file]))
        {
            $file_arr["type"] = "D";
            $data[] = $file_arr;
        }
    }
    
    $csv->write($data, false, ",", "a");
}

$ftp = new FtpUtility(FTP_SERVER, FTP_USER, FTP_PASSWORD);

$csv = new CsvUtility(LOG_CHANGE_FILE);
$csv->write(array("header" => array("name","file", "size", "type")));
write_change_log(PROJECT_PATH, $ftp, $csv, FTP_PROJECT_PATH, PROJECT_PATH, FTP_PROJECT_PATH);

echo 1; exit;