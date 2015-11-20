        function expand (filename, context) {
            var dir = context.dir || context.fileList.getCurrentDirectory();
            $.ajax(OC.linkTo('files_compress', 'ajax/extract.php'), {
                type: 'POST',
                data: {
                    filename: filename,
                    dir: dir
                },
                dataType: 'json',
                success: function(s) {
                    if (s.status == "success") { 
                        alert('Extraction was successful.');
                    } else {
                        alert('Could not extract.');
                    }
                },
                error: function(s) {
                    alert('An error occurred.');
                },
	});
}



