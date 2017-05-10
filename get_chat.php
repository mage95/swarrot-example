<?php
    //get chat
    $myfile = 'chat_log.txt';
    $handle = fopen($myfile, 'r');
    $lines = fread($handle,filesize($myfile));
    $lines = explode("\n",$lines);
    $arr = array();
    foreach ($lines as $line){
        $arr[] = json_decode($line, true);
    }
    $data = json_encode($arr);

    echo $data;

    $handle = fopen($myfile, 'w') or die('Cannot open file:  '.$myfile);
    $data = '';
    fwrite($handle, $data);
