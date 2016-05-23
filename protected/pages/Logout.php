<?php
class Logout extends MainPage {		
	public function onLoad ($param) {		
		if (!$this->User->isGuest) {
            $this->Application->getModule ('auth')->logout();			            	            
		}
        $this->redirect('Login');
	}	
}
?>