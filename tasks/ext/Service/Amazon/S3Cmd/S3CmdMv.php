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
 * Service_Amazon_S3CmdMv class.
 * 
 * @author    Anton Stöckl <anton@stoeckl.de>
 * @version   $Revision$
 * @package   phing.tasks.ext
 * @extends Service_Amazon_S3Cmd
 */
class S3CmdMv extends Service_Amazon_S3Cmd 
{
    public function execute()
    {
        $this->_setOperation('mv');
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
            $srcObjType = $this->_validateObjectPath(Service_Amazon_S3Cmd::BUCKET_OBJECT_OPTIONAL, $this->_source);
            if ($srcObjType === false) {
                throw new BuildException('Source is not valid.');
            }
            if ($srcObjType === self::OBJECT_IS_DIR || $srcObjType === true) {
                if ($this->_recursive !== true) {
                    throw new BuildException('Source seems to be a directory but "recursive" is not set.');
                }
            }
        } else {
            throw new BuildException('Source is required but missing.');
        }
        
        if (!empty($this->_destination)) {
            $dstObjType = $this->_validateObjectPath(Service_Amazon_S3Cmd::BUCKET_OBJECT_OPTIONAL, $this->_destination);
            if ($dstObjType === false) {
                throw new BuildException('Destination is not valid.');
            }
            if ($srcObjType === self::OBJECT_IS_DIR || $srcObjType === true) {
                if ($dstObjType === self::OBJECT_IS_FILE) {
                    throw new BuildException('Source seems to be a directory but destination seems to be a file.');
                }
            } 
        } else {
            throw new BuildException('Destination is required but missing.');
        }
    }
}
