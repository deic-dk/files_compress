<?php
/**
 * ownCloud - files_compress
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Lars Næsbye Christensen (lars.christensen@deic.dk)
 * 
 */


// Include our class of library functions
OC::$CLASSPATH['OC_Files_Archive_Util'] ='apps/files_compress/lib/archiveutils.php';

	if(\OCP\User::isLoggedIn() ){
		\OCP\Util::addScript('files_compress', 'fileactions'); // load our actions script
}

