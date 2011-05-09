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
 * Service_Amazon_S3CmdGet class.
 * 
 * @author    Anton Stöckl <anton@stoeckl.de>
 * @version   $Revision$
 * @package   phing.tasks.ext
 * @extends Service_Amazon_S3Cmd
 */
class S3CmdGet extends Service_Amazon_S3Cmd 
{
    public function execute()
    {
        $this->_setOperation('get');
        $this->_showFullCommand();
        $this->_validateOperationParams();
        $this->_executeCommand();
    }
    
    protected function _validateOperationParams()
    {
        if ($this->_skipStrictChecks === true) {
            return true;
        }
        
        if (!empty($this->_source)) {
            $srcObjTypeBucket = $this->_validateObjectPath(Service_Amazon_S3Cmd::BUCKET_OBJECT_REQUIRED, $this->_source);
            if ($srcObjTypeBucket === false) {
                throw new BuildException('Source is not valid.');
            }
            if ($srcObjTypeBucket === self::OBJECT_IS_DIR || $srcObjTypeBucket === true) {
                throw new BuildException('Source must be an S3 object (file), not a directory.');
            }
        } else {
            throw new BuildException('Source is required but missing.');
        }
        
        if (!empty($this->_destination)) {
            $dstObjTypeBucket = $this->_validateObjectPath(Service_Amazon_S3Cmd::BUCKET_OBJECT_OPTIONAL, $this->_destination);
            if ($dstObjTypeBucket !== false) {
                throw new BuildException('Destination must not be a S3 bucket.');
            }
            $dstObjTypeLocal = $this->_validateObjectPath(Service_Amazon_S3Cmd::LOCAL_OBJECT_REQUIRED, $this->_destination);
            if ($dstObjTypeLocal === Service_Amazon_S3Cmd::OBJECT_IS_DIR) {
                throw new BuildException('Destination must not be a directory.');
            } else if ($dstObjTypeLocal === Service_Amazon_S3Cmd::OBJECT_IS_FILE) {
                if ($this->_force !== true) {
                    throw new BuildException('Destination file exists, use "force" to ovewrite.');
                }
            }
        } else {
            throw new BuildException('Destination is required but missing.');
        }
        
    }
}
