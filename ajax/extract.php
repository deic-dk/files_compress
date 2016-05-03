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
    
    $filenameWOext = strtolower(pathinfo($filename, PATHINFO_FILENAME));
    $mime          = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $archive_dir = $tank_dir . $user . "/files" . $dir . "/";
    $extract_dir = $archive_dir . $filenameWOext . "/";
    
    $success = FALSE;
    if ($mime === 'zip' || $mime === 'gz' || $mime === 'tgz' || $mime === 'tar' || $mime === 'bz2') {
        $phar = new PharData($archive_dir . $filename);
        if ($phar->extractTo($extract_dir, null, true)) {
            $success = TRUE;
        }
    }
    
    if ($success) {
        OCP\JSON::success(); //FIXME: deprecated in 8.1: Use a AppFramework JSONResponse instead
    } else {
        OCP\JSON::error(); //FIXME: deprecated in 8.1: Use a AppFramework JSONResponse instead
    }
}

