# files_compress

An app for handling archive files within ownCloud. Requires ownCloud 7 or newer.

This app adds an Extract function for files with one of the appropriate extensions (zip, gz, tar, bz2, rar), and a Compress function for others. Compression and extraction is handled server-side, using PHP.

These two functions are accessed from the usual list of File Actions.

## Installation instructions
Copy the app to the **owncloud/apps/** directory. Make sure the web server can write to the user directory - this is needed for temporary files.

### To handle RAR archives
Go to http://php.net/manual/en/rar.installation.php for instructions on installation of RAR support
