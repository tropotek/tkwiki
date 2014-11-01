<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A class that can carry out Linux system commands.
 *
 * Note: In order for this to work you must have ssh
 * rsa keys on both systems to avoid the password prompt.
 *
 *
 * @package Tk
 */
class Tk_Util_Exec extends Tk_Object
{
    
    /**
     * scp from the dev derver to a remote server.
     *
     * @param string $srcHostPath
     * @param string $dstHostPath
     * @throws RuntimeException
     */
    static function scp($srcHostPath, $dstHostPath)
    {
        $cmd = sprintf("scp %s %s", escapeshellcmd($srcHostPath), escapeshellcmd($dstHostPath));
        return self::system($cmd);
    }
    
    /**
     * Execute commands on a remote server using ssh.
     *
     * Note: In order for this to work you must have ssh
     *   rsa keys (private/public) on both systems to avoid the password prompt.
     *
     *  Command: ssh-keygen -t rsa
     *
     * The private key would reside in the webserver user ~/.ssh/id_rsa file and
     * the public key would reside in the $sshUser ~/.ssh/authorized_keys file
     *
     * @param string $cmd
     * @param string $sshUser
     * @param string $sshServer
     * @return String
     * @throws RuntimeException
     */
    static function sshExec($cmd, $sshUser, $sshServer)
    {
        $error = 0;
        $return = '';
        $sshCmd = sprintf("ssh %s@%s \"%s\" 2>&1", escapeshellcmd($sshUser), escapeshellcmd($sshServer), $cmd);
        exec($sshCmd, $return, $error);
        $return = implode("\n", $return);
        if ($error) {
            throw new RuntimeException($return);
        }
        return $return;
    }
    
    /**
     * Wrapper for the exe() command
     *
     * @param string $cmd
     * @return string
     * @throws RuntimeException
     */
    static function exec($cmd)
    {
        $error = 0;
        $return = '';
        exec($cmd . ' 2>&1', $return, $error);
        $return = implode("\n", $return);
        if ($error) {
            throw new RuntimeException($return);
        }
        return $return;
    }
    
    /**
     * Wrapper for the exe() command
     *
     * @param string $cmd
     * @return string
     * @throws RuntimeException
     */
    static function execOut($cmd)
    {
        $retval = '';
        $error = '';

        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );

        $resource = proc_open($cmd . ' 2>&1', $descriptorspec, $pipes, null, $_ENV);
        if (is_resource($resource))
        {
            $stdin = $pipes[0];
            $stdout = $pipes[1];
            $stderr = $pipes[2];

            while (! feof($stdout))
            {
                $str = fgets($stdout);
                $retval .= $str;
                print($str);
            }

            while (! feof($stderr))
            {
                $str = fgets($stderr);
                $error .= $str;
                print($str);
            }

            fclose($stdin);
            fclose($stdout);
            fclose($stderr);

            $exit_code = proc_close($resource);
        }

        if (!empty($error)) {
            throw new Exception($error);
        }
        return $retval;
    }
    
    /**
     * Wrapper for the exe() command to run commands in the packground.
     *
     * @param string $cmd
     * @return integer Returns the PID
     */
    static function backgroundExec($cmd)
    {
        $command = $cmd . ' > /dev/null 2>&1 & echo $!';
        exec($command, $op);
        $pid = (int)$op[0];
        if ($pid != "")
            return $pid;
        return false;
    }
}