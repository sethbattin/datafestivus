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

// you could do this.  But you would have to implement a db-based store first.
//$config['db_store']['schema'] = 'rtc_store';
//$config['db_store']['host'] = 'localhost';
//$config['db_store']['user'] = 'username';
//$config['db_store']['pass'] = 'password';

return $config;