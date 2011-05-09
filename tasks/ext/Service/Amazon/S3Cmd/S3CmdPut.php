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

require_once dirname(dirname(__FILE__)) . '/S3Cmd.php';

/**
 * Service_Amazon_S3CmdPut class.
 * 
 * @author    Anton Stöckl <anton@stoeckl.de>
 * @version   $Revision$
 * @package   phing.tasks.ext
 * @extends Service_Amazon_S3Cmd
 */
class S3CmdPut extends Service_Amazon_S3Cmd 
{
    public function execute()
    {
        $this->_setOperation('put');
        $this->_assembleFileset();
        $this->_showFullCommand();
        $this->_validateOperationParams();
        $this->_executeCommand();
    }
    
    protected function _assembleFileset()
    {
        if (!empty($this->_filesets)) {
			$objects = array();
			
			foreach ($this->_filesets as $fs) {
	            if (!($fs instanceof FileSet)) {
	                continue;
	            }

				$ds = $fs->getDirectoryScanner($this->getProject());
				$objects = array_merge($objects, $ds->getIncludedFiles());
			}
			
			$fromDir = $fs->getDir($this->getProject())->getAbsolutePath();
			
            $files = '';
			foreach ($objects as $object) {
                $path = realpath($fromDir . DIRECTORY_SEPARATOR . $object);
                if (!file_exists($path)) {
                    continue;
                }
                $files .= (!empty($files)) ? ' ' . $path : $path;
			}
            $this->setSource(implode(' ', $objects));
            
            if (!empty($files)) {
                $this->setSource($files);
            } else {
                throw new BuildException('Fileset contains no files.');
            }
            
            $this->_setHasFileset(true);
        }
    }
    
    protected function _validateOperationParams()
    {
        if ($this->_skipStrictChecks === true) {
            return true;
        }
        
        if ($this->_hasFileset === true) {
            // all fine
        } else if (!empty($this->_source)) {
            $srcObjTypeLocal = $this->_validateObjectPath(Service_Amazon_S3Cmd::LOCAL_OBJECT_REQUIRED, $this->_source);
            if ($srcObjTypeLocal === false) {
                throw new BuildException('Source is not valid.');
            } else if ($srcObjTypeLocal === Service_Amazon_S3Cmd::OBJECT_IS_DIR) {
                if ($this->_recursive !== true) {
                    throw new BuildException('Source seems to be a directory but "recursive" is not set.');
                }
            }
        } else {
            throw new BuildException('Source or fileset is required but missing.');
        }
        
        if (!empty($this->_destination)) {
            $dstObjTypeBucket = $this->_validateObjectPath(Service_Amazon_S3Cmd::BUCKET_OBJECT_OPTIONAL, $this->_destination);
            if ($dstObjTypeBucket === false) {
                throw new BuildException('Destination is not valid.');
            }
            if ($srcObjTypeLocal === Service_Amazon_S3Cmd::OBJECT_IS_FILESET) {
                if ($dstObjTypeBucket === Service_Amazon_S3Cmd::OBJECT_IS_FILE) {
                    throw new BuildException('Source is a fileset but destination is a file.');
                }
            } 
        } else {
            throw new BuildException('Destination is required but missing.');
        }
        
    }
}
