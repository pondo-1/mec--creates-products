<?php
class PEDipa_Logger
{

  private
    $file,
    $timestamp,
    $line_max = 100000;

  public function __construct($filename)
  {
    if (is_file($filename)) {
      $this->file = $filename;
    } else {
      file_put_contents($filename, "");
    }
    date_default_timezone_set('Europe/Berlin');
    self::setTimestamp('Y-m-d H:i:s');
  }

  public function setTimestamp($format)
  {
    $this->timestamp = date($format) . " ; ";
  }

  public function setMaxline($max)
  {
    $this->line_max = $max;
  }

  public function putLog($insert)
  {
    // Read the entire file into an array
    $file_lines = file($this->file, FILE_IGNORE_NEW_LINES);
    $line_count = count($file_lines);


    if (isset($this->timestamp)) {
      if ($line_count == $this->line_max) {
        unset($file_lines[0]);
        file_put_contents($this->file, implode(PHP_EOL, $file_lines) . PHP_EOL);
      } elseif ($line_count > $this->line_max) {
        for ($i = 0; $i < ($line_count - $this->line_max + 1); $i++) {
          unset($file_lines[$i]);
        }
        file_put_contents($this->file, implode(PHP_EOL, $file_lines) . PHP_EOL);
      }
      file_put_contents($this->file,  $this->timestamp . $insert  . PHP_EOL, FILE_APPEND);
    } else {
      trigger_error("Timestamp not set", E_USER_ERROR);
    }
  }



  public function getLog()
  {
    $content = @file_get_contents($this->file);
    return $content;
  }
}
