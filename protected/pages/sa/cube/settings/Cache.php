<?php
prado::using ('Application.pagecontroller.sa.settings.CCache');
class Cache extends CCache {    
	public function onLoad($param) {		
		parent::onLoad($param);				        
    }    
}