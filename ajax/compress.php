<?php

/*
 * files_compress, ownCloud archive handling app
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

OCP\JSON::checkLoggedIn();

if (OCP\App::isEnabled('files_compress')) {
        $filename = $_POST["filename"];
        $dir      = $_POST["dir"];
        $user     = \OCP\USER::getUser();
        $tank_dir = "/tank/data/owncloud/";
        $user_dir = $tank_dir . $user . "/";
        $temp_dir = $user_dir . "fc_tmp/";
        $ext      =  ".zip";

        $archive_dir    = str_replace("//", "/", $user_dir . "files" . $dir . "/");
        $compress_entry = $archive_dir . $filename;
        
        $tempfile = $temp_dir . $filename . $ext;

        $zipfile  = $compress_entry . $ext;
        
        $success = FALSE;
        
        // we should do our dirty work in a tempdir
        if (!file_exists($temp_dir)) {
                mkdir($temp_dir);
        }
        $compress_entry      = $compress_entry;

        $zip = new ZipArchive();
        $zip->open($tempfile, ZipArchive::CREATE);

        if (is_dir($compress_entry) === true) {

                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($compress_entry), RecursiveIteratorIterator::SELF_FIRST);

                if ($temp_dir) {

                    $arr = explode(DIRECTORY_SEPARATOR, $compress_entry);
                    $maindir = $arr[count($arr)- 1];

                    $compress_entry = "";
                    for ($i=0; $i < count($arr) - 1; $i++) {
                        $compress_entry .= DIRECTORY_SEPARATOR . $arr[$i];
                    }

                    $compress_entry = substr($compress_entry, 1);

                    $zip->addEmptyDir($maindir);

                }

                foreach ($files as $file) {
                    // Ignore "." and ".." folders
                    if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                        continue;

                    $file = realpath($file);

                    if (is_dir($file) === true) {
                        $zip->addEmptyDir(str_replace($compress_entry . DIRECTORY_SEPARATOR, '', $file . DIRECTORY_SEPARATOR));
                    } else if (is_file($file) === true) {
                        $zip->addFromString(str_replace($compress_entry . DIRECTORY_SEPARATOR, '', $file), file_get_contents($file));
                    }
                }
            }
            else if (is_file($compress_entry) === true) {
                $zip->addFromString(basename($compress_entry), file_get_contents($compress_entry));
            }


        $zip->close();

        // move everything in place
        rename($tempfile, $zipfile);
        
        // cleanup by deleting all files and temp directory
        
        $files = glob($temp_dir . '{,.}*', GLOB_BRACE);
        foreach ($files as $file) { // iterate files
                if (is_file($file)) {
                        unlink($file);
                } // delete file
        }
        
        if (file_exists($temp_dir)) {
                rmdir($temp_dir);
        }
        
        // success is determined by .zip file in proper place
        
        $success = file_exists($zipfile);

        if ($success) {
                        OCP\JSON::success();
        } else {
                        OCP\JSON::error();
        }
}
