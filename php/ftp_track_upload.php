<?php

$file_csv = $_POST['filename'];

$file_data = CsvUtility::fetchCSV($file_csv);
$done_count = $total_bytes = $upload_bytes =  0;

if ($file_data)
{
    foreach($file_data as $arr)
    {
        $total_bytes += $arr['size'];
        if ($arr['is_done'])
        {
            $upload_bytes += $arr['size'];
            $done_count++;
        }
    }
}

header('Cache-Control: no-cache');
echo json_encode(array(
    "total_count" => count($file_data),
    "done_count" => $done_count,
    "total_bytes" => $total_bytes,
    "upload_bytes" => $upload_bytes,
));
exit;