<?php

// INSTALLATION:
// 1. copy this ./.config.php
// 2. edit values as required

$config = [];

// these values will work without any changes, assuming file permissions are ok.

// the type of storage
$config['rtc_store'] = '\DataFestivus\RTCStore\CSV';

// storage-specific settings
$config['csv_store']['file_path'] = __DIR__ . '/rtc_store.csv';

// or you could do this.  Much nicer than stupid CSV files.
//$df_config['rtc_store'] = '\DataFestivus\RTCStore\Adapter\SQLite';
//$df_config['sqlite']['file_path'] = __DIR__ . '/rtc_store.sq3';

// or you could do this.  It would probably be pretty easy to make one like
// the sqlite implementation.  
//$config['db_store']['schema'] = 'rtc_store';
//$config['db_store']['host'] = 'localhost';
//$config['db_store']['user'] = 'username';
//$config['db_store']['pass'] = 'password';

return $config;