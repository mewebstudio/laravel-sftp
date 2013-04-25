<?php
/**
 * Simple FTP Class
 * 
 * @package SFTP
 * @name SFTP
 * @version 1.0
 * @author Shay Anderson 05.11
 * @link http://www.shayanderson.com/php/simple-ftp-class-for-php.htm
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * SFTP is free software and is distributed WITHOUT ANY WARRANTY
 *
 * @modifications Muharrem ERİN / me@mewebstudio.com / 10.10.2012
 * 
 */
class SFTP {

	// singleton instance 
	private static $instance;

	/**
	 * FTP host
	 *
	 * @var string $_host
	 */
	private static $_host;

	/**
	 * FTP port
	 *
	 * @var int $_port
	 */
	private static $_port = 21;

	/**
	 * FTP password
	 *
	 * @var string $_pwd
	 */
	private static $_pwd;
	
	/**
	 * FTP stream
	 *
	 * @var resource $_id
	 */
	private static $_stream;

	/**
	 * FTP timeout
	 *
	 * @var int $_timeout
	 */
	private static $_timeout = 90;

	/**
	 * FTP user
	 *
	 * @var string $_user
	 */
	private static $_user;

	/**
	 * Last error
	 *
	 * @var string $error
	 */
	public static $error;

	/**
	 * FTP passive mode flag
	 *
	 * @var bool $passive
	 */
	public static $passive = false;

	/**
	 * SSL-FTP connection flag
	 *
	 * @var bool $ssl
	 */
	public static $ssl = false;

	/**
	 * System type of FTP server
	 *
	 * @var string $system_type
	 */
	public static $system_type;

	/**
	 * Initialize connection params
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param int $port
	 * @param int $timeout (seconds)
	 */
	public static function make($host = null, $user = null, $password = null, $port = 21, $timeout = 90) {
		static::$_host = $host;
		static::$_user = $user;
		static::$_pwd = $password;
		static::$_port = (int)$port;
		static::$_timeout = (int)$timeout;

		if(!self::$instance)
		{
			self::$instance = new self();
		}

		return self::$instance; 
	}

	/**
	 * Auto close connection
	 */
	public function  __destruct() {
		$this->close();
	}

	/**
	 * Change currect directory on FTP server
	 *
	 * @param string $directory
	 * @return bool
	 */
	public function cd($directory = null) {
		// attempt to change directory
		if(@ftp_chdir(static::$_stream, $directory)) {
			// success
			return true;
		// fail
		} else {
			static::$error = "Failed to change directory to \"{$directory}\"";
			return false;
		}
	}

	/**
	 * Set file permissions
	 *
	 * @param int $permissions (ex: 0644)
	 * @param string $remote_file
	 * @return false
	 */
	public function chmod($permissions = 0, $remote_file = null) {
		// attempt chmod
		if(@ftp_chmod(static::$_stream, $permissions, $remote_file)) {
			// success
			return true;
		// failed
		} else {
			static::$error = "Failed to set file permissions for \"{$remote_file}\"";
			return false;
		}
	}

	/**
	 * Close FTP connection
	 */
	public function close() {
		// check for valid FTP stream
		if(static::$_stream) {
			// close FTP connection
			ftp_close(static::$_stream);

			// reset stream
			static::$_stream = false;
		}
	}

	/**
	 * Connect to FTP server
	 *
	 * @return bool
	 */
	public function connect() {
		// check if non-SSL connection
		if(!static::$ssl) {
			// attempt connection
			if(!static::$_stream = ftp_connect(static::$_host, static::$_port, static::$_timeout)) {
				// set last error
				static::$error = "Failed to connect to {static::$_host}";
				return false;
			}
		// SSL connection
		} elseif(function_exists("ftp_ssl_connect")) {
			// attempt SSL connection
			if(!static::$_stream = @ftp_ssl_connect(static::$_host, static::$_port, static::$_timeout)) {
				// set last error
				static::$error = "Failed to connect to {static::$_host} (SSL connection)";
				return false;
			}
		// invalid connection type
		} else {
			static::$error = "Failed to connect to {static::$_host} (invalid connection type)";
			return false;
		}

		// attempt login
		if(@ftp_login(static::$_stream, static::$_user, static::$_pwd)) {
			// set passive mode
			ftp_pasv(static::$_stream, (bool)static::$passive);

			// set system type
			static::$system_type = ftp_systype(static::$_stream);

			// connection successful
			return true;
		// login failed
		} else {
			static::$error = "Failed to connect to {static::$_host} (login failed)";
			return false;
		}
	}

	/**
	 * Delete file on FTP server
	 *
	 * @param string $remote_file
	 * @return bool
	 */
	public function delete($remote_file = null) {
		// attempt to delete file
		if(@ftp_delete(static::$_stream, $remote_file)) {
			// success
			return true;
		// fail
		} else {
			static::$error = "Failed to delete file \"{$remote_file}\"";
			return false;
		}
	}

	/**
	 * Download file from server
	 *
	 * @param string $remote_file
	 * @param string $local_file
	 * @param int $mode
	 * @return bool
	 */
	public function get($remote_file = null, $local_file = null, $mode = FTP_ASCII) {
		// attempt download
		if(@ftp_get(static::$_stream, $local_file, $remote_file, $mode)) {
			// success
			return true;
		// download failed
		} else {
			static::$error = "Failed to download file \"{$remote_file}\"";
			return false;
		}
	}

	/**
	 * Get list of files/directories in directory
	 *
	 * @param string $directory
	 * @return array
	 */
	public function ls($directory = null) {
		$list = array();

		// attempt to get list
		if($list = @ftp_nlist(static::$_stream, $directory)) {
			// success
			return $list;
		// fail
		} else {
			static::$error = "Failed to get directory list";
			return array();
		}
	}

	/**
	 * Create directory on FTP server
	 *
	 * @param string $directory
	 * @return bool
	 */
	public function mkdir($directory = null) {
		// attempt to create dir
		if(@ftp_mkdir(static::$_stream, $directory)) {
			// success
			return true;
		// fail
		} else {
			static::$error = "Failed to create directory \"{$directory}\"";
			return false;
		}
	}

	/**
	 * Upload file to server
	 *
	 * @param string $local_path
	 * @param string $remote_file_path
	 * @param int $mode
	 * @return bool
	 */
	public function put($local_file = null, $remote_file = null, $mode = FTP_ASCII) {
		// attempt to upload file
		if(@ftp_put(static::$_stream, $remote_file, $local_file, $mode)) {
			// success
			return true;
		// upload failed
		} else {
			static::$error = "Failed to upload file \"{$local_file}\"";
			return false;
		}
	}

	/**
	 * Get current directory
	 *
	 * @return string
	 */
	public function pwd() {
		return ftp_pwd(static::$_stream);
	}

	/**
	 * Rename file on FTP server
	 *
	 * @param string $old_name
	 * @param string $new_name
	 * @return bool
	 */
	public function rename($old_name = null, $new_name = null) {
		// attempt rename
		if(@ftp_rename(static::$_stream, $old_name, $new_name)) {
			// success
			return true;
		// fail
		} else {
			static::$error = "Failed to rename file \"{$old_name}\"";
			return false;
		}
	}

	/**
	 * Remove directory on FTP server
	 *
	 * @param string $directory
	 * @return bool
	 */
	public function rmdir($directory = null) {
		// attempt remove dir
		if(@ftp_rmdir(static::$_stream, $directory)) {
			// success
			return true;
		// fail
		} else {
			static::$error = "Failed to remove directory \"{$directory}\"";
			return false;
		}
	}
}
// end of SFTP.php