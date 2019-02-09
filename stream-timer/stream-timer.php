#!/usr/bin/php
<?php

  require_once __DIR__.'/vendor/autoload.php';

  use Carbon\Carbon;

  if ($argc !== 2) {
    print 'Please pass the desired time as the first argument.'.PHP_EOL;
    exit(1);
  }

  try {
    $target_time = new Carbon($argv[1]);
  } catch (Exception $e) {
    print $e->getMessage();
    return;
  }

  $config = json_decode(file_get_contents(__DIR__.'/config.json'), true);

  $remaining_time_buffer = '';
  $time_file_path = __DIR__.'/stream-time.txt';

  while (true) {
    if (Carbon::now()->isAfter($target_time)) {
      $resource = fopen($time_file_path, 'w');
      fwrite($resource, $config['overdue_text']);
      fclose($resource);
      print $config['overdue_text'].PHP_EOL;
      break;
    }
    $diff_string = $target_time->diffAsCarbonInterval(Carbon::now());
    $diff_string->format('i:s');
    $remaining_time = ($diff_string->minutes > 9 ? $diff_string->minutes : '0'.$diff_string->minutes) . ':' . ($diff_string->seconds > 9 ? $diff_string->seconds : '0'.$diff_string->seconds);
    if ($remaining_time === $remaining_time_buffer) continue;
    print $remaining_time.PHP_EOL;
    $remaining_time_buffer = $remaining_time;
    $resource = fopen($time_file_path, 'w');
    fwrite($resource, $config['prefix_text'].$remaining_time);
    fclose($resource);
  }
