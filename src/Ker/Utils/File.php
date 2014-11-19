<?php

namespace Ker\Utils;

/**
 * Description of File
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @todo TASK: #58
 */
class File
{

    public static function fileWasReceived($_file)
    {
//UPLOAD_ERR_OK         - There is no error, the file uploaded with success.
//UPLOAD_ERR_INI_SIZE   - The uploaded file exceeds the upload_max_filesize directive in php.ini.
//UPLOAD_ERR_FORM_SIZE  - The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
//UPLOAD_ERR_PARTIAL    - The uploaded file was only partially uploaded.
//UPLOAD_ERR_NO_FILE    - No file was uploaded.
//UPLOAD_ERR_NO_TMP_DIR - Missing a temporary folder.
//UPLOAD_ERR_CANT_WRITE - Failed to write file to disk.
//UPLOAD_ERR_EXTENSION  - A PHP extension stopped the file upload.
        return ($_file["error"] === UPLOAD_ERR_OK );
    }

    public static function fileWasSent($_file)
    {
        return ($_file["error"] !== UPLOAD_ERR_NO_FILE );
    }

    public static function typeIsAcceptable($_file, $_types)
    {
        return (in_array($_file["type"], $_types));
    }

    public static function getModifyTimestamp($_path)
    {
        $path = realpath($_path);

        if (!$path) {
            return NULL;
        }

        return filemtime($path);
    }

    //TODO: dokumentacja
    public static function getDirsChecksums($_dir, $_opts = array())
    {
        $dirs = (is_array($_dir) ? $_dir : array($_dir));
        sort($dirs);

        $files = array();

        $ignore = (isset($_opts["ignore"]) ? $_opts["ignore"] : null);
        $substDir = (isset($_opts["substDir"]) ? $_opts["substDir"] : false);

        foreach ($dirs as $dir) {
            $dirLength = strlen($dir);

            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $path => $fileinfo) {
                if (!$fileinfo->isFile()) {
                    continue;
                }

                $key = ($substDir ? substr($path, $dirLength) : $path);

                if (DIRECTORY_SEPARATOR !== "/") {
                    $key = str_replace(DIRECTORY_SEPARATOR, "/", $key);
                }

                if ($ignore && preg_match($ignore, $key)) {
                    continue;
                }

                $files [$key] = sha1_file($path);
            }
        }

        ksort($files);

        return $files;
    }

}
