<?php

/*                                                                                                                                
 * files_compress, ownCloud file decompression app 
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

if (OCP\App::isEnabled('files_compress')){
	$filename	= $_POST["filename"];
	$dir	= $_POST["dir"];
	$user 	= \OCP\USER::getUser();
	$tank_dir="/tank/data/owncloud/";


	$filenameWOext= strtolower(pathinfo($filename, PATHINFO_FILENAME));

	$archive_dir = $tank_dir.$user."/files".$dir."/";
	$extract_dir = $archive_dir.$filename;
        $tarfile = $extract_dir.'.tar';

	$success = FALSE;

		$phar = new PharData($tarfile);
		if ($phar->buildFromDirectory($extract_dir)) { 
                    $phar->compress(Phar::GZ);
                    if (file_exists($tarfile)) {
        unlink($tarfile); } //delete the intermediate file

		    $success = TRUE;
		}
	

	if ($success) {
		OCP\JSON::success(array("data" => array('filename' => $filename, 'archivedir' => $archive_dir, 'dir' => $dir, 'user' => $user, 'mime' => $mime, 'workingdir' => getcwd())));
	} 
            else {
		OCP\JSON::error(array("data" => array('filename' => $filename, 'archivedir' => $archive_dir, 'dir' => $dir, 'user' => $user, 'mime' => $mime, 'workingdir' => getcwd())));
	}
}

