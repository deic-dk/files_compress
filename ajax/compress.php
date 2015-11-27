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
    $user_dir = $tank_dir . $user ."/";    
    $archive_dir    = $user_dir . "files" . $dir . "/";
    $compress_entry = $archive_dir . $filename;
    $tarfile        = $compress_entry . '.tar';

    $success = FALSE;
    
    $phar = new PharData($tarfile);
    
    
    if (is_dir($compress_entry)) {
        $phar->buildFromDirectory($compress_entry);
    } else {
        $phar->addFile($compress_entry);
    }
    
    $phar->compress(Phar::GZ);
    
    if (file_exists($tarfile)) {
        unlink($tarfile); //delete the intermediate file
    } 
    
    // success is determined by nonexistence of the tar file
    
     $success = !file_exists($tarfile);
    
    if ($success) {
        OCP\JSON::success();
    } else {
        OCP\JSON::error();
    }
    
}

