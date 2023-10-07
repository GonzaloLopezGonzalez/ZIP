<?php

class Zip{

  private $zip;
  private $zipName = '';

  public function __construct(string $zipName)
  {
     $this->checkIfZipModuleIsLoaded();
     $this->checkZipExtensionInZipName($zipName);
     $this->zipName = $zipName;
     $this->zip = new ZipArchive();

     $this->zip->open($this->zipName,ZipArchive::CREATE);
  }

  private function checkIfZipModuleIsLoaded()
  {
    if(!extension_loaded('zip')){
      throw new Exception('No está cargado el módulo de ficheros zip');
    }
  }

  private function checkZipExtensionInZipName(string $zipName)
  {
    if (strpos($zipName, 'zip') === false){
       throw new Exception('El nombre del fichero tiene que tener extension .zip');
    }
  }

  public function addFile(string $fileName, $password = null)
  {
    $this->zip->addFile($fileName,basename($fileName));
    if (!empty($password)){
      $this->zip->setEncryptionName(basename($fileName), ZipArchive::EM_AES_256, $password);
    }
  }

  public function addFiles(array $arrFileNames, $password = null)
  {
    $notExistingFiles = '';
    foreach ($arrFileNames as $fileName){
      if (file_exists($fileName)){
          $this->addFile($fileName, $password);
      }else{
        $notExistingFiles .= $fileName.',';
      }
    }
    $notExistingFiles = rtrim($notExistingFiles,',');
    if (str_word_count($notExistingFiles) > 0 ){
        throw new Exception('Los siguientes ficheros no existen: '.$notExistingFiles.' y no se han comprimido');
    }
  }

  public function addFilesByFolder(string $folder, $password = null)
  {
     if(is_dir($folder)){
        if (substr($folder, -1) !== '/'){
          $folder .= '/';
        }
        $options = array('add_path' => "$folder", 'remove_all_path' => TRUE);
        if (!empty($password)){
          $options['enc_method'] = ZipArchive::EM_AES_256;
          $options['enc_password'] = $password;
        }
        $this->zip->addGlob("$folder*.*", GLOB_BRACE, $options);
     }else{
         throw new Exception('No existe la ruta.');
    }
  }

  public function extractTo(string $extractPath, $password = null)
  {
    if ($this->zip->open($this->zipName) === TRUE) {
      if (!is_dir($extractPath)){
          mkdir($extractPath, 0755, true);
      }
      if (empty($password) OR $this->zip->setPassword($password)){
        if(!$this->zip->extractTo($extractPath)){
          throw new Exception('Password incorrecta.');
        }
      }
    }
  }

  public function __destruct()
  {
    $this->zip->close();
  }
}
