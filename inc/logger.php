<?php

/*********************
 *  Debug functions  *
 *********************/

// set default log level if not defined
if (!defined('LOGFILE'))
    define('LOGFILE', 'guacmanager.log');

if (!defined('LOGGER_LEVEL'))
    define('LOGGER_LEVEL', 'INFO');

class Logger {
  private static function write($text)
  {
    try {
      file_put_contents(LOGFILE, $text."\n", FILE_APPEND | LOCK_EX);
    } catch (Throwable $t) {
      error_log('[ERROR] Log file not writable!');
    }
  }

  /**
   * Log a message into error log
   * @param  string $message Message
   * @param  string $prefix  Prefix (inserted in start line)
   * @param  string $context Context (append at the end)
   * @return void
   */
  public static function message($message, $prefix='', $context='', $level='') {

    // if level is defined,
    if ($level != '') {
      // authorized levels
      $levels = ['DEBUG','INFO','WARN','ERROR','FATAL'];

      // check level configured
      $current = array_search(LOGGER_LEVEL, $levels);
      if ($current !== false) {
        // check chosen level
        $l = array_search($level, $levels);
        // do not print
        if ($l !== false && $l < $current)
          return;
      }
    }

    $output = date("Y-m-d H:i:s").' ';

    if ($prefix)
      $output .= '[' . $prefix . '] ';

    $output .= $message;

    if ($context)
      $output .= ' ('.$context.')';

    self::write($output);
  }


  /**
   * Dump a variable in log file
   * @param  mixed $object The variable to dump
   * @param  string $name  Variable name to print before dump
   * @return void
   */
  public static function var_dump($object, $name='')
  {
    ob_start();                    // start buffer capture
    var_dump($object);             // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture

    if ($name != '')
      $name = '$'.$name.' = ';

    self::write($name.$contents);
  }


  /**
   * Log a debug message
   * @param  string $message Message
   * @param  string $context Context (optionnal)
   * @return void
   */
  public static function debug($message, $context='') {
    self::message($message, 'DEBUG', $context, 'DEBUG');
  }


  /**
   * Log an info message
   * @param  string $message Message
   * @param  string $context Context (optionnal)
   * @return void
   */
  public static function info($message, $context='') {
    self::message($message, 'INFO', $context, 'INFO');
  }


  /**
   * Log a warning message
   * @param  string $message Message
   * @param  string $context Context (optionnal)
   * @return void
   */
  public static function warn($message, $context='') {
    self::message($message, 'WARNING', $context, 'WARN');
  }


  /**
   * Log an error message
   * @param  string $message Message
   * @param  string $context Context (optionnal)
   * @return void
   */
  public static function error($message, $context='') {
    self::message($message, 'ERROR', $context, 'ERROR');
  }


  /**
   * Log a fatal error message
   * @param  string $message Message
   * @param  string $context Context (optionnal)
   * @return void
   */
  public static function fatal($message, $context='') {
    self::message($message, 'FATAL', $context, 'FATAL');
  }
}
