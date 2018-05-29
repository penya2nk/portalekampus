<?php
prado::using ('Application.pagecontroller.m.settings.CCache');
class Cache extends CCache {    
	public function onLoad($param) {		
		parent::onLoad($param);				        
    }    
}