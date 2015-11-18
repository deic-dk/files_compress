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

namespace OCA\FilesCompress\AppInfo;

	if(\OCP\User::isLoggedIn() ){
		\OCP\Util::addScript('files_compress', 'fileactions');
}

