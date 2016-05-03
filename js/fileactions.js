        function files_compress_expand(filename, context) {
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
                        FileList.reload();
                    } else {
                        alert('Could not extract.');
                    }
                },
                error: function(s) {
                    alert('An error occurred.');
                },
            });
        }

        function files_compress_compress(filename, context) {
            var dir = context.dir || context.fileList.getCurrentDirectory();
            $.ajax(OC.linkTo('files_compress', 'ajax/compress.php'), {
                type: 'POST',
                data: {
                    filename: filename,
                    dir: dir
                },
                dataType: 'json',
                success: function(s) {
                    if (s.status == "success") {
                        FileList.reload();
                    } else {
                        alert('Could not compress.');
                    }
                },
                error: function(s) {
                    alert('An error occurred.');
                },
            });
        }


        $(document).ready(function() {
            if (typeof FileActions !== 'undefined') {

                FileActions.register('application/zip', t('files_compress', 'Extract'), OC.PERMISSION_READ, '',
                    function(filename, context) {
                        files_compress_expand(filename, context)
                    });
                FileActions.register('application/x-gzip', t('files_compress', 'Extract'), OC.PERMISSION_READ, '',
                    function(filename, context) {
                        files_compress_expand(filename, context)
                    });
                FileActions.register('application/x-compressed', t('files_compress', 'Extract'), OC.PERMISSION_READ, '',
                    function(filename, context) {
                        files_compress_expand(filename, context)
                    });

                FileActions.register('all', t('files_compress', 'Compress'), OC.PERMISSION_READ, '',
                    function(filename, context) {
                        files_compress_compress(filename, context)
                    });

            }
            // Add action to top bar (visible when files are selected)
            $('#app-content-files #headerName .selectedActions').prepend(
                '<a class="tag btn btn-xs btn-default" id="tag" href=""><i class="icon icon-compress"></i>' + t('files_compress', ' Compress') + '</a>&nbsp;');

        });
