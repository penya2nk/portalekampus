<?php

class NModalPanel extends TActivePanel {
    
    public function onInit($param) {
        
        parent::onInit($param);
        
        $this->getPage()->getClientScript()->registerPradoScript("prado");
        $this->getPage()->getClientScript()->registerPradoScript("effects");
        $this->getPage()->getClientScript()->registerScriptFile('NModalPanel',$this->publishAsset("Assets/NModalPanel/NModalPanel.js"));
    }
    
    public function onLoad($param) {
        
        parent::onLoad($param);
        
        // initially hides the modalpanel
        $this->Attributes->style = "display:none;";
    }
    
    public function renderEndTag($writer) {
        
        parent::renderEndTag($writer);
        
        $script = "<script type=\"text/javascript\">/*<![CDATA[*/ var modal_" . $this->ClientID . " = new Modal.Box('" . $this->ClientID .  "') /*]]>*/</script>";
        $writer->write($script);
    }
    
    public function Show() {
       // execute an javascript to close the modal box
       $script = "modal_" . $this->ClientID . ".show();";
	   $this->Page->ClientScript->registerEndScript( $this->ClientID, $script );
    }
    
    public function Hide() {
        // execute an javascript to close the modal box
       $script = "modal_" . $this->ClientID . ".hide();";
	   $this->Page->ClientScript->registerEndScript( $this->ClientID, $script );
    }
}


?>