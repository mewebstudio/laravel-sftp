# SFTP Bundle
SFTP is a ftp bundle for the Laravel framework.

## Installation

        php artisan bundle:install sftp

## Bundle Registration

Add the following to your **/application/bundles.php** file:

	'sftp' => array('auto' => true, 'handles' => 'sftp'),

## Usage

        $ftp = SFTP::make('ftp.example.com', 'username', 'password');

        // connect to FTP server
        if($ftp->connect()) {
              print "Connection successful";
        
              // download a file from FTP server
              // will download file "somefile.php" and  
              // save locally as "localfile.php"
              if($ftp->get("readme.md", "readme.md")) {
                    print "File downloaded";
              } else {
                    print "<br />Download failed: " . $ftp->error;
              }
        
              // upload file to FTP server
              // will upload file "local.php" and
              // save remotely as "remote.php"
              if($ftp->put("readme.md", "readme.md")) {
                    print "Filed uploaded";
              } else {
                    print "<br />Upload failed: " . $ftp->error;
              }
        } else {
              // connection failed, display last error
              print "Connection failed: " . $ftp->error;
        }

## Other useful FTP class methods:

        // change directory to "/mydir"
        $ftp->cd("mydir");
        
        // set file permissions for file "remote.php"
        $ftp->chmod(0777, "remote.php");
        
        // delete file "remote.php"
        $ftp->delete("remote.php");
        
        // get list of files/directories in directory "/mydir"
        print_r($ftp->ls("mydir"));
        
        // create directory "/mydir2"
        $ftp->mkdir("mydir2");
        
        // get current directory
        print $ftp->pwd();
        
        // rename file "remote.php" to "rem.php"
        $ftp->rename("remote.php", "rem.php");
        
        // remove directory "/mydir2"
        $ftp->rmdir("mydir2");

## Useful FTP class properties:

        // display last FTP error (if error occurred)
        print $ftp->error;
        
        // set FTP passive mode on (use before connect() method)
        $ftp->passive = true;
        
        // use SSL-FTP connection (use before connect() method)
        $ftp->ssl = true;
        
        // set server system type (use after connect() method)
        $system_type = $ftp->system_type;

## Further information
Thanks, Shay Anderson http://www.shayanderson.com/php/simple-ftp-class-for-php.htm
This bundle is maintained by Muharrem ERİN (me@mewebstudio.com). If you have any questions or suggestions, email me. You can always grab the latest version from http://github.com/mewebstudio/laravel-sftp