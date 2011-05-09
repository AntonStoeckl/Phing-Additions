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
 * Service_Amazon_S3CmdSync class.
 * 
 * @author    Anton Stöckl <anton@stoeckl.de>
 * @version   $Revision$
 * @package   phing.tasks.ext
 * @extends Service_Amazon_S3Cmd
 */
class S3CmdSync extends Service_Amazon_S3Cmd 
{
    public function execute()
    {
        $this->_setOperation('sync');
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
            $srcObjTypeBucket = $this->_validateObjectPath(Service_Amazon_S3Cmd::BUCKET_OBJECT_OPTIONAL, $this->_source);
            $srcObjTypeLocal = $this->_validateObjectPath(Service_Amazon_S3Cmd::LOCAL_OBJECT_REQUIRED, $this->_source);
            if ($srcObjTypeBucket === false && $srcObjTypeLocal === false) {
                throw new BuildException('Source is not valid.');
            }
        } else {
            throw new BuildException('Source is required but missing.');
        }
        
        if (!empty($this->_destination)) {
            $dstObjTypeBucket = $this->_validateObjectPath(Service_Amazon_S3Cmd::BUCKET_OBJECT_OPTIONAL, $this->_destination);
            $dstObjTypeLocal = $this->_validateObjectPath(Service_Amazon_S3Cmd::LOCAL_OBJECT_REQUIRED, $this->_destination);
            if ($dstObjTypeBucket === false && $dstObjTypeLocal === false) {
                throw new BuildException('Destination is not valid.');
            }
        } else {
            throw new BuildException('Destination is required but missing.');
        }
        
        if (!(($srcObjTypeBucket && $dstObjTypeLocal && ($dstObjTypeLocal === Service_Amazon_S3Cmd::OBJECT_IS_DIR)) || 
             ($srcObjTypeLocal && $dstObjTypeBucket && ($srcObjTypeLocal === Service_Amazon_S3Cmd::OBJECT_IS_DIR)))) {
            throw new BuildException('One of source / destination must be a local directory and the other a S3 bucket.');    
        }
        
    }
}
