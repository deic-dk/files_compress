<?php

class OC_Files_Archive_Util {

function readDirectory($path) {
	if ($handle = opendir($path)) {
		$tree = [];
	    while (false !== ($entry = readdir($handle))) {
	    	if (!($entry==="." || $entry==="..")){
	    		if (is_dir($path.$entry)){
	    			$array['dir'] = true;
	    			$array['name'] = $entry;
	    			$array['entry'] = readDirectory($path.$entry."/");
	    			array_push($tree, $array);
	    		} else {
	    			$array['dir'] = false;
	    			$array['name'] = $entry;
	    			array_push($tree, $array);
	    		}
	    		unset($array);
	    	}
	    }
	    closedir($handle);
	    return $tree;
	}
} 


}

