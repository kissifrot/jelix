<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * base class of all builder form classes generated by the jform compiler.
 *
 * a builder form class is a class which help to generate a form for the output
 * (html form for example)
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
abstract class jFormsBuilderBase {
    /**
     * a form object
     * @var jFormsBase
     */
    protected $_form;

    /**
     * the action selector
     * @var string
     */
    protected $_action;

    /**
     * params for the action
     * @var array
     */
    protected $_actionParams = array();

    /**
     * form name
     */
    protected $_name;

    /**
     * @param jFormsBase $form a form object
     * @param string $action action selector where form will be submit
     * @param array $actionParams  parameters for the action
     */
    public function __construct($form, $action, $actionParams){
        $this->_form = $form;
        $this->_action = $action;
        $this->_actionParams = $actionParams;
        $this->_name = jFormsBuilderBase::generateFormName();
    }

    public function getName(){ return  $this->_name; }

    abstract public function outputHeader($params);

    abstract public function outputFooter();

    abstract public function outputControl($ctrl);
    abstract public function outputControlLabel($ctrl);

    public static function generateFormName(){
        static $number = 0;
        $number++;
        return 'jform'.$number;
    }
}


/**
 * HTML form builder
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
abstract class jFormsHtmlBuilderBase extends  jFormsBuilderBase {

    public function outputHeader($params){
        $url = jUrl::get($this->_action, $this->_actionParams, 2); // retourne le jurl correspondant
        echo '<form action="'.$url->scriptName . $url->pathInfo.'" method="POST" name="'. $this->_name.'" onsubmit="return jForms.verifyForm(this)">';
        if(count($url->params)){
            echo '<div>';
            foreach ($url->params as $p_name => $p_value) {
                echo '<input type="hidden" name="'. $p_name .'" value="'. htmlspecialchars($p_value) .'"/>', "\n";
            }
            echo '</div>';
        }
        echo '<script type="text/javascript"> 
//<[CDATA[
', $this->getJavascriptCheck($params[0],$params[1]),'
//]]>
</script>';
    }

    public function outputFooter(){
        echo '</form>';
    }

    public function outputControlLabel($ctrl){
        if($ctrl->type == 'output' || $ctrl->type == 'checkboxes' || $ctrl->type == 'radiobuttons'){
            echo htmlspecialchars($ctrl->label);
        }else if($ctrl->type != 'submit'){
            $id = $this->_name.'_'.$ctrl->ref;
            echo '<label for="'.$id.'">'.htmlspecialchars($ctrl->label).'</label>';
        }
    }

    public function outputControl($ctrl){
        $id = ' name="'.$ctrl->ref.'" id="'.$this->_name.'_'.$ctrl->ref.'"';
        $readonly = ($ctrl->readonly?' readonly="readonly"':'');
        switch($ctrl->type){
        case 'input':
            $value = $this->_form->getData($ctrl->ref);
            if($value === null)
                $value = $ctrl->defaultValue;
            echo '<input type="text"',$id,$readonly,' value="',htmlspecialchars($value),'"/>';
            break;
        case 'checkbox':
            $value = $this->_form->getData($ctrl->ref);
            if($value ===null)
                $value = ($ctrl->defaultValue == 'true');
            if($value){
                $v=' checked="checked"';
            }else{
                $v="";
            }
            echo '<input type="checkbox"',$id,$readonly,$v,' value="true"/>';
            break;
        case 'checkboxes':
            $i=0;
            $id=$this->_name.'_'.$ctrl->ref.'_';
            $attrs=' name="'.$ctrl->ref.'[]" id="'.$id;
            $value = $this->_form->getData($ctrl->ref);
            if($value == null){
                $value = $ctrl->selectedValues;
            }

            if(is_array($value) && count($value) == 1)
                $value = $value[0];

            if(is_array($value)){
                foreach($ctrl->datasource->getDatas() as $v=>$label){
                    echo '<input type="checkbox"',$attrs,$i,'" value="',htmlspecialchars($v),'"';
                    if(in_array($v,$value)) 
                        echo ' checked="checked"';
                    echo $readonly,'/><label for="',$id,$i,'">',htmlspecialchars($label),'</label>';
                    $i++;
                }
            }else{
                foreach($ctrl->datasource->getDatas() as $v=>$label){
                    echo '<input type="checkbox"',$attrs,$i,'" value="',htmlspecialchars($v),'"';
                    if($v == $value) 
                        echo ' checked="checked"';
                    echo $readonly,'/><label for="',$id,$i,'">',htmlspecialchars($label),'</label>';
                    $i++;
                }
            }
            break;
        case 'radiobuttons':
            $i=0;
            $id=' name="'.$ctrl->ref.'" id="'.$this->_name.'_'.$ctrl->ref.'_';
            $value = $this->_form->getData($ctrl->ref);
            if($value === null){
                if(count($ctrl->selectedValues) == 1)
                    $value = $ctrl->selectedValues[0];
            }
            foreach($ctrl->datasource->getDatas() as $v=>$label){
                echo '<input type="radio"',$id,$i,'" value="',htmlspecialchars($v),'"',($v==$value?' checked="checked"':''),$readonly,'/>';
                echo '<label for="',$this->_name,'_',$ctrl->ref,'_',$i,'">',htmlspecialchars($label),'</label>';
                $i++;
            }
            break;
        case 'menulist':
            echo '<select',$id,$readonly,' size="1">';
            $value = $this->_form->getData($ctrl->ref);
            if($value === null){
                if(count($ctrl->selectedValues) == 1)
                    $value = $ctrl->selectedValues[0];
            }
            foreach($ctrl->datasource->getDatas() as $v=>$label){
                echo '<option value="',htmlspecialchars($v),'"',($v==$value?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
            }
            echo '</select>';
            break;
        case 'listbox':
            if($ctrl->multiple){
                echo '<select name="',$ctrl->ref,'[]" id="',$this->_name,'_',$ctrl->ref,'"',$readonly,' size="',$ctrl->size,'" multiple="multiple">';
                $value = $this->_form->getData($ctrl->ref);
                if($value == null){
                    $value = $ctrl->selectedValues;
                }

                if(is_array($value) && count($value) == 1)
                    $value = $value[0];

                if(is_array($value)){
                    foreach($ctrl->datasource->getDatas() as $v=>$label){
                        echo '<option value="',htmlspecialchars($v),'"',(in_array($v,$value)?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
                    }
                }else{
                    foreach($ctrl->datasource->getDatas() as $v=>$label){
                        echo '<option value="',htmlspecialchars($v),'"',($v==$value?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
                    }
                }
                echo '</select>';
            }else{
                $value = $this->_form->getData($ctrl->ref);
                if($value == null){
                    $value = $ctrl->selectedValues;
                }

                if(is_array($value)){
                    if(count($value) >= 1)
                        $value = $value[0];
                    else
                        $value ='';
                }

                echo '<select',$id,$readonly,' size="',$ctrl->size,'">';
                foreach($ctrl->datasource->getDatas() as $v=>$label){
                    echo '<option value="',htmlspecialchars($v),'"',($v==$value?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
                }
                echo '</select>';
            }
            break;
        case 'textarea':
            $value = $this->_form->getData($ctrl->ref);
            if($value === null)
                $value = $ctrl->defaultValue;
            echo '<textarea',$id,$readonly,'>',htmlspecialchars($value),'</textarea>';
            break;
        case 'secret':
            echo '<input type="password"',$id,$readonly,' value="',htmlspecialchars($this->_form->getData($ctrl->ref)),'"/>';
            break;
        case 'output':
            $value = $this->_form->getData($ctrl->ref);
            if($value === null)
                $value = $ctrl->defaultValue;
            echo '<input type="hidden"',$id,' value="',htmlspecialchars($value),'"/>';
            echo htmlspecialchars($value);
            break;
        case 'upload':
            echo '<input type="file"',$id,$readonly,' value=""/>'; // ',htmlspecialchars($this->_form->getData($ctrl->ref)),'
            break;
        case 'submit':
            echo '<button type="submit"',$id,'>',htmlspecialchars($ctrl->label),'</button>';
            break;
        }

        if ($ctrl->hasHelp) {
            if($ctrl->type == 'checkboxes' || ($ctrl->type == 'listbox' && $ctrl->multiple)){
                $name=$ctrl->ref.'[]';
            }else{
                $name=$ctrl->ref;
            }
            echo '<span class="jforms-help"><a href="javascript:jForms.showHelp(\''. $this->_name.'\',\''.$name.'\')">?</a></span>';
        }
    }


    abstract public function getJavascriptCheck($errDecorator,$helpDecorator);
}


?>