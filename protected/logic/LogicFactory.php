<?php

class LogicFactory extends TModule {	
	/**
	*
	* objek db
	*/
	private $db;	
	public function init ($config) {
		$this->db = $this->Application->getModule ('db')->getLink();	
	}				
	/**
	* digunakna untuk membuat objek dari sebuah kelas
	*
	*/
	public function getInstanceOfClass ($className) {		
		switch ($className) {
			case 'Users' :
				prado::using ('Application.logic.Logic_Users');
				return new Logic_Users ($this->db);
			break;		
            case 'Setup' :
				prado::using ('Application.logic.Logic_Setup');
				return new Logic_Setup ($this->db);
			break;
            case 'Penanggalan' :
				prado::using ('Application.logic.Logic_Penanggalan');
				return new Logic_Penanggalan ($this->db);
			break;
            case 'DMaster' :
				prado::using ('Application.logic.Logic_DMaster');
				return new Logic_DMaster ($this->db);
			break; 
            case 'Mahasiswa' :
				prado::using ('Application.logic.Logic_Mahasiswa');
				return new Logic_Mahasiswa ($this->db);
			break;            
            case 'Akademik' :
				prado::using ('Application.logic.Logic_Akademik');
				return new Logic_Akademik ($this->db);
			break;
            case 'KRS' :
				prado::using ('Application.logic.Logic_KRS');
				return new Logic_KRS ($this->db);
			break;
            case 'Kuesioner' :
				prado::using ('Application.logic.Logic_Kuesioner');
				return new Logic_Kuesioner ($this->db);
			break;
            case 'Nilai' :
				prado::using ('Application.logic.Logic_Nilai');
				return new Logic_Nilai ($this->db);
			break;
            case 'Finance' :
				prado::using ('Application.logic.Logic_Finance');
				return new Logic_Finance ($this->db);
			break;
            case 'Report' :
				prado::using ('Application.logic.Logic_Report');
				return new Logic_Report ($this->db);
			break;
            case 'ReportSPMB' :
				prado::using ('Application.logic.Logic_ReportSPMB');
				return new Logic_ReportSPMB ($this->db);
			break;
            case 'ReportAkademik' :
				prado::using ('Application.logic.Logic_ReportAkademik');
				return new Logic_ReportAkademik ($this->db);
			break;
            case 'ReportKRS' :
				prado::using ('Application.logic.Logic_ReportKRS');
				return new Logic_ReportKRS ($this->db);
			break;
            case 'ReportNilai' :
				prado::using ('Application.logic.Logic_ReportNilai');
				return new Logic_ReportNilai ($this->db);
			break;
            case 'Mail' :
				prado::using ('Application.logic.Logic_Mail');
				return new Logic_Mail ($this->db);
			break;
            case 'Log' :
				prado::using ('Application.logic.Logic_Log');
				return new Logic_Log ($this->db);
			break;
            case 'Forum' :
				prado::using ('Application.logic.Logic_Forum');
				return new Logic_Forum ($this->db);
			break;
			default :
				throw new Exception ("Logic_Factory.php :: $className tidak di ketahui");
		}
	}
}
?>