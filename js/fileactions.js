
/*
 * Copyright (c) 2015, written by Lars Næsbye Christensen, DeIC.
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * This file contains the extraction call itself. Moved over from DeiC OC7 theme. 
 * Not functional yet (16/11/2015)
 */
 
 (function() {


        /**
         * Extract Ajax call to files_compress/ajax/extract.php
         * @param parent filename
         * @param parent context
         */
        extract: function(filename, context) {
            var dir = context.dir || context.fileList.getCurrentDirectory();
            $.ajax(OC.linkTo('files_compress', 'ajax/extract.php'), {
                type: 'POST',
                data: {
                    filename: filename,
                    dir: dir
                },
                dataType: 'json',
                success: function(s) {
                    if (s['status'] == "success") {
                        alert('Extraction was successful.');
                    } else {
                        alert('Could not extract.');
                    }
                },
                error: function(s) {
                    alert('Error');
                }
            });
        }


    }

);

