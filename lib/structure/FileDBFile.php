<?php

abstract class FileDBFile {
  
  const EXTENSION = 'fdb';
  
  abstract protected function getFilename();

  protected function write(){
    global $settings;
    $dir = $settings['db']['folder'];
    $filename = $dir . '/' . $this->getFilename();

    FileDBHandler::_static_reset('fileExists');
    FileDBHandler::_static_reset('getRows');
    FileDBHandler::_static_reset('readFile_' . $filename);
    
    return FileDBHandler::writeFile($filename, $this, true);
  }
}

