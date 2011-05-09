<?php
/*
 * $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once "phing/Task.php";

/**
 * Abstract Service_Amazon_S3Cmd class.
 *
 * Provides common methods and properties to all of the S3Cmd tasks
 * @author    Anton Stöckl <anton@stoeckl.de>
 * @version   $Revision$
 * @package   phing.tasks.ext
 * @abstract
 */
abstract class Service_Amazon_S3Cmd extends Task
{
    const BUCKET_ONLY = 0;
    const BUCKET_OBJECT_OPTIONAL = 1;
    const BUCKET_OBJECT_REQUIRED = 2;
    const LOCAL_OBJECT_REQUIRED = 3;

    const OBJECT_IS_FILE = 1;
    const OBJECT_IS_DIR = 2;
    const OBJECT_IS_FILESET = 3;

    /**
     * Path to the s3cmd command.
     *
     * @var string
     * @access protected
     */
    protected $_command = null;

    /**
     * Path to the s3cmd config file.
     *
     * @var string
     * @access protected
     */
    protected $_config = null;

    /**
     * The s3cmd operation to execute.
     *
     * @var string
     * @access protected
     */
    protected $_operation = null;

    /**
     * Command options.
     *
     * @var string
     * @access protected
     */
    protected $_options = null;

    /**
     * The installed version of the s3cmd command.
     *
     * @var string
     * @access protected
     */
    protected $_version = null;

    /**
     * Whether the full output of the s3cmd should be shown.
     *
     * @var bool
     * @access protected
     */
    protected $_verbose = false;

    /**
     * Where to store the output result of the command.
     *
     * @var string
     * @access protected
     */
    protected $_outputProperty = null;

    /**
     * The source object for this action.
     *
     * @var string
     * @access protected
     */
    protected $_source = null;

    /**
     * The destination object for this action.
     *
     * @var string
     * @access protected
     */
    protected $_destination = null;

    /**
     * Collection of filesets, used for uploading multiple files.
     *
     * @var array
     * @access protected
     */
    protected $_filesets = array();

    /**
     * Whether we have a non empty fileset.
     *
     * @var bool
     * @access protected
     */
    protected $_hasFileset = false;

    /**
     * Whether we want to store on S3 with reduced redundancy.
     *
     * @var bool
     * @access protected
     */
    protected $_reducedRedundancy = false;

    /**
     * Whether to do a dry run.
     *
     * @var bool
     * @access protected
     */
    protected $_dryRun = false;

    /**
     * Whether we want a recursive action.
     *
     * @var bool
     * @access protected
     */
    protected $_recursive = false;

    /**
     * Whether we want force overwrite and other dangerous operations.
     *
     * @var bool
     * @access protected
     */
    protected $_force = false;

    /**
     * Whether to skip strict source and destination checks.
     *
     * @var bool
     * @access protected
     */
    protected $_skipStrictChecks = false;

    /**
     * The full command to execute.
     *
     * @var string
     * @access protected
     */
    protected $_fullCommand = null;

    /**
     * Sets the path to the s3cmd command.
     *
     * @param string $command
     * @access public
     */
    public function setCommand($command)
    {
        $this->_command = $command;
    }

    /**
     * Sets the path to the s3cmd config file.
     *
     * @param string $config
     * @access public
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * Sets the s3cmd operation to execute.
     *
     * @param string $operation
     * @access protected
     */
    protected function _setOperation($operation)
    {
        $this->_operation = $operation;
    }

    /**
     * Sets the command options.
     *
     * @param string $options
     * @access public
     */
    public function setOptions($options)
    {
        $this->_options = $options;
    }

    /**
     * Sets the installed version of the s3cmd command.
     *
     * @param string $version
     * @access protected
     */
    protected function _setVersion($version)
    {
        $this->_version = $version;
    }

    /**
     * Sets whether the full output of the s3cmd should be shown.
     *
     * @param bool $verbose
     * @access public
     */
    public function setVerbose($verbose)
    {
        $this->_verbose = (bool) $verbose;
    }

    /**
     * Sets where to store the output result of the command.
     *
     * @param string $outputProperty
     * @access public
     */
    public function setOutputProperty($outputProperty)
    {
        $this->_outputProperty = $outputProperty;
    }

    /**
     * Sets the source object for this action.
     *
     * @param string $source
     * @access public
     */
    public function setSource($source)
    {
        $this->_source = $source;
    }

    /**
     * Sets the destination object for this action.
     *
     * @param string $destination
     * @access public
     */
    public function setDestination($destination)
    {
        $this->_destination = $destination;
    }

    /**
     * Sets the installed version of the s3cmd command.
     *
     * @param bool $hasFilest
     * @access protected
     */
    protected function _setHasFileset($hasFileset)
    {
        $this->_hasFileset = (bool) $hasFileset;
    }

    /**
     * Sets whether we want to store on S3 with reduced redundancy.
     *
     * @param string $reducedRedundancy
     * @access public
     */
    public function setReducedRedundancy($reducedRedundancy)
    {
        $this->_reducedRedundancy = (bool) $reducedRedundancy;
    }

    /**
     * Sets whether to do a dry run.
     *
     * @param string $dryRun
     * @access public
     */
    public function setDryRun($dryRun)
    {
        $this->_dryRun = (bool) $dryRun;
    }

    /**
     * Sets whether we want a recursive action.
     *
     * @param string $recursive
     * @access public
     */
    public function setRecursive($recursive)
    {
        $this->_recursive = (bool) $recursive;
    }

    /**
     * Sets whether we want force overwrite and other dangerous operations..
     *
     * @param string $force
     * @access public
     */
    public function setForce($force)
    {
        $this->_force = (bool) $force;
    }

    /**
     * Sets whether to skip strict source and destination checks.
     * The checks are wrong in some cases - S3 could do the operation.
     *
     * @param string $skipStrictChecks
     * @access public
     */
    public function setSkipStrictChecks($skipStrictChecks)
    {
        $this->_skipStrictChecks = (bool) $skipStrictChecks;
    }

    /**
     * creator for _filesets
     *
     * @access public
     * @return FileSet
     */
    public function createFileset()
    {
        $num = array_push($this->_filesets, new FileSet());
        return $this->_filesets[$num-1];
    }

	/**
     * getter for _filesets
     *
     * @access public
     * @return array
     */
    public function getFilesets()
    {
        return $this->_filesets;
    }

    /**
     * Normalizes the version (if set) by removing the dots and casting to int.
     *
     * @access protected
     * @return int
     */
    protected function _getNormalizedVersion()
    {
        if (!empty($this->_version)) {
            return (int) str_replace('.', '', $this->_version);
        }

        return 0;
    }

    /**
     * Getter for the full command to execute.
     * Builds it first, if not yet set.
     *
     * @access protected
     * @return string
     */
    protected function _getFullCommand()
    {
        if (empty($this->_fullCommand)) {
            $this->_buildFullCommand();
        }

        return $this->_fullCommand;
    }

    /**
     * Main entry point, doesn't do anything
     *
     * @access public
     * @final
     * @return void
     */
    final public function main()
    {
        $this->_validateConfig($this->_config);
        $this->_validateCommand($this->_command);
        $this->execute();
    }

    /**
     * Entry point to children tasks
     *
     * @access public
     * @abstract
     * @return void
     */
    abstract public function execute();

    /**
     * Validates if s3cmd is installed and configured.
     *
     * @param string $command
     * @access protected
     * @throws BuildException
     */
    protected function _validateCommand($command)
    {
        if (is_executable($command)) {
            exec("{$command} --version 2>&1", $output, $rc);
            if (preg_match('/s3cmd version (.+)/', $output[0], $matches)) {
                $this->_setVersion($matches[1]);
                unset ($output);
                $cfg = null;
                if (!empty($this->_config)) {
                    $cfg = " -c {$this->_config}";
                }
                exec("{$command}{$cfg} --dump-config 2>&1", $output, $rc);
                if (!preg_match('/\[default\]/', $output[0])) {
                    throw new BuildException('You have no stored configuration for your s3cmd. Run s3cmd --configure first.');
                }
            } else {
                throw new BuildException('This "command" is not the s3cmd: ' . $command);
            }
        } else {
            throw new BuildException('This "command" is not executable: ' . $command);
        }
    }

    protected function _validateConfig($config)
    {

    }

    /**
     * Validates if $path is a valid s3 URI.
     *
     * @param int $type
     * @param string $path
     * @access protected
     * @return bool
     * @throws BuildException
     */
    protected function _validateObjectPath($type, $path)
    {
        switch ($type) {
            case self::BUCKET_ONLY:
                if (preg_match('@^s3://([^/]+)$@', $path, $matches)) {
                    return $this->_isBucket($matches[1]);
                }
                break;
            case self::BUCKET_OBJECT_OPTIONAL:
                if (preg_match('@^s3://([^/]+)$@', $path, $matches) || preg_match('@^s3://([^/]+/(.*))$@', $path, $matches)) {
                    $rcb = $this->_isBucket($matches[1]);
                    $rco = (!empty($matches[2])) ? $this->_isObject($matches[2]) : true;
                    if ($rcb && !empty($rco)) {
                        return $rco;
                    } else {
                        return false;
                    }
                }
                break;
            case self::BUCKET_OBJECT_REQUIRED:
                if (preg_match('@^s3://([^/]+/(.+))$@', $path, $matches)) {
                    $rcb = $this->_isBucket($matches[1]);
                    $rco = $this->_isObject($matches[2]);
                    if ($rcb && !empty($rco)) {
                        return $rco;
                    } else {
                        return false;
                    }
                }
                break;
            case self::LOCAL_OBJECT_REQUIRED:
                if (!empty($path) && !preg_match('@^s3://([^/]+).*$@', $path)) {
                    return $this->_isLocalObject($path);
                }
                break;
            default:
                throw new BuildException('Invalid type for object path validation: ' . $type);
        }

        return false;
    }

    /**
     * Checks if $string is a bucket (pattern).
     *
     * @param string $string
     * @access protected
     * @return bool
     */
    protected function _isBucket($string)
    {
        if (preg_match('@\d+\.\d+\.\d+\.\d+@', $string)) {
            return false;
        }

        if (preg_match('@^[\w][\w\.-_]{2,254}$@', $string)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if $string is an object (pattern).
     *
     * @param string $string
     * @access protected
     * @return mixed
     */
    protected function _isObject($string)
    {
        if (preg_match('@^[\w\.-_]+/$@', $string) ) {
            return self::OBJECT_IS_DIR;
        }

        if (preg_match('@^[\w\.-_]+$@', $string)) {
            return self::OBJECT_IS_FILE;
        }

        return false;
    }

    /**
     * Checks if $string is an existing local object (file or dir).
     *
     * @param string $string
     * @access protected
     * @return mixed
     */
    protected function _isLocalObject($string)
    {
        if (is_dir($string)) {
            return self::OBJECT_IS_DIR;
        }

        if (is_file($string)) {
            return self::OBJECT_IS_FILE;
        }

        return false;
    }

    /**
     * Executes the s3cmd command and returns the exit code.
     *
     * @access protected
     * @return int Return code from execution.
     * @throws BuildException
     */
    protected function _executeCommand()
    {
        $command = $this->_getFullCommand();

        $output = array();
        $return = null;
        exec($command, $output, $return);

        $lines = '';
        foreach ($output as $line) {
            if (!empty($line)) {
                $lines .= "\r\n" . $line;
            }
        }
        $lines .= "\r\n";

        if ($return != 0) {
            throw new BuildException('s3cmd exited with a fatal error, return code: ' . $return . $lines);
        } else if (preg_match('@ERROR:@', $output[0])) {
            throw new BuildException($lines);
        } else {
            if ($this->_verbose === true) {
                $this->log('Command output was: ' . $lines, Project::MSG_INFO);
            }
            if (!empty($this->_outputProperty)) {
                $this->project->setProperty($this->_outputProperty, implode("\n", $output));
            }
        }

        return $return;
    }

    /**
     * Builds the full command to execute.
     *
     * @access protected
     * @return string
     * @throws BuildException
     */
    protected function _buildFullCommand()
    {
        if ($this->_command === null) {
            throw new BuildException('The "command" attribute is missing or undefined.');
        } else if ($this->_operation === null) {
            throw new BuildException('The "operation" attribute is missing or undefined.');
        }

        $options = '';
        if ($this->_config !== null) {
            $options = ' -c ' . $this->_config;
        }
        if ($this->_options !== null) {
            $options = ' ' . $this->_options;
        }
        if ($this->_dryRun === true) {
            $options .= ' -n';
        }
        if ($this->_recursive === true) {
            $options .= ' -r';
        }
        if ($this->_force === true) {
            $options .= ' -f';
        }
        if ($this->_reducedRedundancy === true) {
            if ($this->_getNormalizedVersion() >= 100) {
                $options .= ' --rr';
            } else {
                throw new BuildException("Your version of s3cmd ({$this->_command} version {$this->_version}) does not support reduced redundancy.");
            }
        }

        $this->setOptions($options);

        $options .=  (!empty($this->_source)) ? ' ' . $this->_source : null;
        $options .=  (!empty($this->_destination)) ? ' ' . $this->_destination : null;

        escapeshellcmd($options);
        $options .= ' 2>&1';

        $this->_fullCommand =  $this->_command . ' ' . $this->_operation . $options;
    }

    /**
     * Displays the full command to execute via log.
     *
     * @access protected
     */
    protected function _showFullCommand()
    {
        $output = 'Executing command: ' . $this->_getFullCommand();
        $this->log($output, Project::MSG_INFO);
    }

}

