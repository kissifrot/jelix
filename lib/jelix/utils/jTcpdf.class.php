<?php
/**
* @package     jelix
* @subpackage  utils
* @author      Julien Issler
* @contributor Laurent Jouanneau
* @copyright   2007-2009 Julien Issler, 2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.0
*/

define('K_TCPDF_EXTERNAL_CONFIG',true);
define("K_PATH_MAIN", LIB_PATH.'tcpdf/');
define("K_PATH_URL", $GLOBALS['gJConfig']->urlengine['basePath']);
define("K_PATH_FONTS", LIB_PATH.'pdf-fonts/');
define("K_PATH_CACHE", K_PATH_MAIN."cache/");
define("K_CELL_HEIGHT_RATIO", 1.25);
define("K_SMALL_RATIO", 2/3);
require_once (LIB_PATH.'tcpdf/tcpdf.php');

/**
 * sub-class of TCPDF, for better Jelix integration (error handling) and easy save to disk feature.
 * @package    jelix
 * @subpackage utils
 * @since 1.0
 */
class jTcpdf extends TCPDF {

    public function __construct($orientation='P', $unit='mm', $format='A4', $encoding=null) {

        if($encoding === null)
            $encoding = $GLOBALS['gJConfig']->charset;

        parent::__construct($orientation, $unit, $format, ($encoding == 'UTF-8' || $encoding == 'UTF-16'), $encoding);

        $this->setHeaderFont(array('helvetica','',10));
        $this->setFooterFont(array('helvetica','',10));
        $this->setFont('helvetica','',10);
    }


    /**
     * Throw an exception when an error occurs, instead of die()
     * @param string $msg The error's message generated by TCPDF
     */
    public function Error($msg){
        throw new Exception($msg);
    }


    /**
     * Method to save the current document to a file on the disk
     * @param string $filename The target filename
     * @param string $path The target path where to store the file
     * @return boolean TRUE if success, else throws a jException
     */
    public function saveToDisk($filename,$path){

        if(!is_dir($path))
            throw new jException('jelix~errors.file.directory.notexists',array($path));
        
        if(!is_writable($path))
           throw new jException('jelix~errors.file.directory.notwritable',array($path));

        if(file_put_contents(realpath($path).'/'.$filename, $this->Output('','S')))
           return true;       

        throw new jException('jelix~errors.file.write.error',array($path.'/'.$filename,''));

    }

}