<?php


namespace App\Helpers;

use App\Helpers\Interfaces\ValidatorInterface;
use PDOException;

class FileUploadValidator extends Validator implements ValidatorInterface
{
    /**
     * @param $name
     * @param $file
     */
    public function validateUploadUserFileName($name, $file)
    {
        try{
            $stmt = $this->db->prepare("SELECT * FROM files WHERE name = ? AND format = ? AND user_id = ?");

            $var = Helpers::sanitize($name);
            $userId = $_SESSION['id'];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $stmt->execute([$var, $extension, $userId]);
            $count = $stmt->rowCount();

        } catch (\PDOException $exception){
            echo 'Internal Error';
            exit();
        }

        if($count != 0){
            $this->errors[] = 'A File With This Name And Format Already Exists';
        }
    }

    /**
     * @param $file
     * @param $name
     */
    public function validateUserFileUpload($file, $name)
    {
        $this->validateFileRequired($file, 'File');
        $this->validateRequired($name, 'Name');

        if(!empty($this->getErrors()) ){
            header('Location: create.php?errors=' . urlencode(json_encode($this->getErrors())) );
            exit();
        }

        $this->validateUploadUserFileName($name, $file);
        $this->validateFileSize($file);
        if(!empty($this->getErrors()) ){
            header('Location: create.php?errors=' . urlencode(json_encode($this->getErrors())) );
            exit();
        }
    }

    public function validateRenameFile($path, $fileId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM files WHERE path = ? AND id NOT IN (?)");
            $var = Helpers::sanitize($path);
            $stmt->execute([$var, $fileId]);
            $count = $stmt->rowCount();

        } catch (PDOException $exception) {
            echo 'Internal Error';
            exit();
        }

        if ($count != 0) {
            $this->errors[] = 'A File With This Name And Format Already Exists' ;
        }
    }

    /**
     * @param $file
     */
    public function validateFileSize($file)
    {
        if ($file["size"] > 8000000) {
            $this->errors[] = "File Exceeds Maximum File Size of 8MB.";
        }
    }

    /**
     * @param $file
     * @param $varName
     */
    public function validateFileRequired($file, $varName)
    {
        if(isset($file['error'])) {
            if ($file['error'] == 4) {
                $this->errors[] ="$varName is required.";
            }
        }
    }

    /**
     * @param $path
     */
    public function validateFileOwnership($path)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM files WHERE path = ? AND user_id = ?");
            $var = Helpers::sanitize($path);
            $userId = $_SESSION['id'];
            $stmt->execute([$var, $userId]);
            $count = $stmt->rowCount();

        } catch (PDOException $exception) {
            echo 'Internal Error';
            exit();
        }

        if ($count == 0) {
            $this->errors[] = 'Access To This File Is Denied' ;
        }
    }
}