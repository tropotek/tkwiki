<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An wrapper clas for the php-ftp module, this extension must be enabled for it to work.
 * Check  phpinfo(); if it does not work.
 *
 *
 * @package Util
 */
class Tk_Util_Ftp extends Tk_Object
{
    
    /**
     * @var resource
     */
    protected $linkId = null;
    
    /**
     * FTP Host
     * @var string
     */
    protected $host = 'localhost';
    
    /**
     * FTP Port
     * @var string
     */
    protected $port = '21';
    
    /**
     * FTP User
     * @var string
     */
    protected $user = 'Anonymous';
    
    /**
     * FTP Password
     * @var string
     */
    protected $pass = 'anonymous@email.com';
    
    /**
     * FTP pasv
     * @var boolean
     */
    protected $pasv = false;
        
    /**
     * FTP Host
     * @var boolean
     */
    protected $isLogin = false;
    
    /**
     * local path for upload or download
     * @var string
     */
    protected $localDir = '';
    
    /**
     * FTP root path of FTP server
     * @var string
     */
    protected $remoteDir = '';
    
    /**
     * FTP current path
     * @var string
     */
    protected $pwd = '/';
    
    
    
    /**
     * __construct
     *
     * @param string $user
     * @param string $pass
     * @param string $host
     * @param string $port
     */
    function __construct($user = 'Anonymous', $pass = 'anonymous@email.com', $host = 'localhost', $port = 21, $pasv = false)
    {
        if (!function_exists('ftp_connect')) {
            throw new Exception('The PHP FTP module is not installed, contact your hosting provider to enable it!');
        }
        
        if($user) $this->user = $user;
        if($pass) $this->pass = $pass;
        if($host) $this->host = $host;
        if($port) $this->port = $port;
        $this->pasv = $pasv;
        $this->login();
        $this->pwd  = $this->pwd();
        $this->rootDir  = $this->rootDir;
    }
    
    /**
     * Login
     *
     * @return Resource
     */
    function login()
    {
        if(!$this->linkId){
            if (!$this->linkId = ftp_connect($this->host, $this->port)) {
                Tk::log("Cannot connect to host: $this->host:$this->port", Tk::LOG_ALERT);
                return;
            }
        }
        if(!$this->is_login){
            if (!$this->is_login = ftp_login($this->linkId, $this->user, $this->pass)) {
                Tk::log("Ftp login faild. Invaid user or password", Tk::LOG_ALERT);
                return;
            }
            if ($this->pasv) {
                ftp_pasv($this->linkId, true);
            }
        }
        return $this->linkId;
    }
    
    /**
     * Returns the system type identifier of the remote FTP server.
     *
     * @return string Returns the remote system type, or FALSE on error
     */
    function systype()
    {
        return ftp_systype($this->linkId);
    }
    
    /**
     * Returns the current directory name
     *
     * This function doesn't always go to the remote server for the PWD.
     * Once called the PWD is cached, and until PHP has a reason to believe the directory has changed any call to ftp_pwd()
     * will return from the cache, even if the remote server has gone away.
     *
     * @return string Returns the current directory name or FALSE on error.
     */
    function pwd()
    {
        $this->login();
        $this->dir = ftp_pwd($this->linkId);
        return $this->dir;
    }
    
    /**
     * Changes to the parent directory
     *
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    function cdup()
    {
        $this->login();
        $isok =  ftp_cdup($this->linkId);
        if($isok) {
            $this->dir = $this->pwd();
        }
        return $isok;
    }
    
    /**
     * Navigate to a remote directory
     *
     * @param string $dir
     * @return boolean
     */
    function cd($dir){
        $this->login();
        $isok = ftp_chdir($this->linkId, $dir);
        if($isok) {
            $this->dir = $dir;
        }
        return $isok;
    }

    /**
     * Cet the current directory to the remote user home
     *
     * @return boolean
     */
    function cdHome()
    {
        $this->login();
        $isok = ftp_chdir($this->linkId, '~');
        if($isok) {
            $this->pwd();
        }
        return $isok;
    }
    
    /**
     * Check if a directory exists on the remote system
     *
     * @param string $dir
     * @return boolean
     */
    function dirExists($dir)
    {
        if (@ftp_chdir($this->linkId, $dir)) {
            $this->cd($this->dir);
            return true;
        } return false;
    }
    
    /**
     * Returns a list of files in the given directory
     * EG:
     *    array(3) {
     *      [0] =>  string(11) "public_html"
     *      [1] =>  string(10) "public_ftp"
     *      [2] =>  string(3) "www"
     *
     * @param string $dir If null the current directory is used
     * @return array Returns an array of filenames from the specified directory on success or FALSE on error.
     */
    function nlist($dir = null)
    {
        $this->login();
        if(!$dir) $dir = ".";
        $arr_dir = ftp_nlist($this->linkId,$dir);
        return $arr_dir;
    }
    
    /**
     * Returns a detailed list of files in the given directory
     * Eg:
     *   array(3) {
     *     [0] => string(65) "drwxr-x---   3 vincent  vincent      4096 Jul 12 12:16 public_ftp"
     *     [1] => string(66) "drwxr-x---  15 vincent  vincent      4096 Nov  3 21:31 public_html"
     *     [2] => string(73) "lrwxrwxrwx   1 vincent  vincent        11 Jul 12 12:16 www -> public_html"
     *   }
     *
     * @param string $dir
     * @return array Returns an array of filenames from the specified directory on success or FALSE on error.
     */
    function rawlist($dir = null)
    {
        $this->login();
        $arr_dir = ftp_rawlist($this->linkId,$dir);
        return $arr_dir;
    }
    
    /**
     * Creates a directory
     *
     * @param string $dir
     * @return Returns the newly created directory name on success or FALSE on error.
     */
    function mkdir($dir)
    {
        $this->login();
        return @ftp_mkdir($this->linkId,$dir);
    }
    
    /**
     * Returns the size of the given file
     *
     * @param string $file
     * @return integer Returns the file size on success, or -1 on error
     */
    function fileSize($file)
    {
        $this->login();
        $size = ftp_size($this->linkId,$file);
        return $size;
    }
    
    /**
     * Set permissions on a file via FTP
     *
     * Using the excellent octdec and decoct functions you can make this easy:
     *   <?php
     *     $mode = "644";
     *     $mode = octdec( str_pad($mode,4,'0',STR_PAD_LEFT) );
     *     ftp_chmod($ftp_stream, $mode, $file);
     *   ?>
     *
     * @param string $file
     * @param octal $mode
     * @return octal Returns the new file permissions on success or FALSE on error.
     */
    function chmod($file, $mode = 0666)
    {
        $this->login();
        return ftp_chmod($this->linkId, $file, $mode);
    }
    
    /**
     * Deletes a file on the FTP server
     *
     * @param string $remoteFile
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    function delete($remoteFile)
    {
        $this->login();
        return ftp_delete($this->linkId, $remoteFile);
    }
    
    /**
     * Downloads a file from the FTP server
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param integer $mode (Default: auto_detect)
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    function get($localFile, $remoteFile, $mode = null)
    {
        $this->login();
        if ($mode === null) {
            $mode = $this->detectMode($remoteFile);
        }
        return ftp_get($this->linkId, $localFile, $remoteFile, $mode);
    }
    
    /**
     * Uploads a file to the FTP server
     *
     * @param string $remoteFile
     * @param string $localFile
     * @param integer $mode (Default: auto_detect)
     * @return boolean Returns TRUE on success or FALSE on failure.
     * @todo:
     */
    function put($remoteFile, $localFile, $mode = null)
    {
        $this->login();
        if ($mode === null) {
            $mode = $this->detectMode($localFile);
        }
        return ftp_put($this->linkId, $remoteFile, $localFile, $mode);
    }
    
    /**
     * Place a string on an FTP server as a file
     *
     * @param string $remoteFile
     * @param string $data
     * @param integer $mode (FTP_ASCII || FTP_BINARY)
     * @return boolean
     */
    function putString($remoteFile, $data, $mode = FTP_ASCII)
    {
        $this->login();
        $tmp = Tk_Config::getTmpPath();
        $tmpfile = tempnam($tmp, 'tmp_');
        $fp = @fopen($tmpfile, 'w+');
        if($fp) {
            fwrite($fp,$data);
            fclose($fp);
        } else {
            return false;
        }
        $isok = $this->put($remoteFile, $tmpfile, $mode);
        @unlink($tmpfile);
        return $isok;
    }
    
    /**
     * Close the FTP connection
     *
     */
    function close()
    {
        @ftp_quit($this->linkId);
    }
    
    /**
     * Try Autodetecting the file transfer mode type.
     *
     * @param string $file
     * @return integer (FTP_BINARY || FTP_ASCII)
     */
    private function detectMode($file)
    {
        $pathParts = pathinfo($file);
       
        if (!isset($pathParts['extension'])) return FTP_BINARY;
        
        switch (strtolower($pathParts['extension'])) {
            case 'am':case 'asp':case 'bat':case 'c':case 'cfm':case 'cgi':case 'conf':
            case 'cpp':case 'css':case 'dhtml':case 'diz':case 'h':case 'hpp':case 'htm':
            case 'html':case 'in':case 'inc':case 'js':case 'm4':case 'mak':case 'nfs':
            case 'nsi':case 'pas':case 'patch':case 'php':case 'php3':case 'php4':case 'php5':
            case 'phtml':case 'pl':case 'po':case 'py':case 'qmail':case 'sh':case 'shtml':
            case 'sql':case 'tcl':case 'tpl':case 'txt':case 'vbs':case 'xml':case 'xrc':
            case 'txt':case 'pde': case 'ini':       // My additions
                return FTP_ASCII;
        }
        return FTP_BINARY;
    }
    
}