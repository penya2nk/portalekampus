<?php
class MainController extends TControl {   
    /**
	* Object Variable "Logic_Finance"	
	*/
	public $Finance;
    /**
     * digunakan untuk membuat berbagai macam object
     */
    public function createObj ($nama_object) {
        switch (strtolower($nama_object)) {                       
            case 'finance' :
                prado::using ('Application.logic.Logic_Finance');				
                $this->Finance = new Logic_Finance ($this->Application->getModule ('db')->getLink());
            break;                                    
        }
    }
}
?>