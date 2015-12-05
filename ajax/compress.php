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
    $filename       = $_POST["filename"];
    $dir            = $_POST["dir"];
    $user           = \OCP\USER::getUser();
    $tank_dir       = "/tank/data/owncloud/";
    $user_dir       = $tank_dir . $user . "/";
    $temp_dir       = $user_dir . "fc_tmp/";
    $archive_dir    = $user_dir . "files" . $dir . "/";
    $compress_entry = $archive_dir . $filename;
    
    $tempfile = $temp_dir . $filename . '.gz';
    $tarfile  = $compress_entry . '.gz';
    
    $success = FALSE;
    
    // we should do our dirty work in a tempdir
    if (!file_exists($temp_dir)) {
        mkdir($temp_dir);
    }
    
    
    $phar = new PharData($tempfile);
    
    if (is_dir($compress_entry)) {
        $phar->buildFromDirectory($compress_entry);
    } else {
        $phar->addFile($compress_entry);
    }
    
    $phar->compress(Phar::GZ);
    
    // move everything in place
    rename($tempfile, $tarfile);
    
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
    
    
    // success is determined by .gz file in proper place
    
    $success = file_exists($tarfile);
    
    if ($success) {
        OCP\JSON::success();
    } else {
        OCP\JSON::error();
    }
    
}

