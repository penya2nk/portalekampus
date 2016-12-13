<?php
prado::using('Application.UserManager');
class Autorisasi extends TModule implements IUserManager {	
	/**
	* @return string name for a guest user	
	*/		
	public function getGuestName () {
		return 'Guest';
	}
	
	/**
	* returns a user instance given the username
	* @param string username, null if it is a guest
	* @return TUser the user instance, null if the specified username is not the user database
	*
	*/
	public function getUser ($username=null) {				
		if ($username === null) {
			$user = new TUser ($this);
			$user->setIsGuest(true);
			return $user;
		}else {			
			$user = new TUser ($this);						
			$um = new UserManager();
			$um->setUser($username);			
			$user->setIsGuest (false);			
			$user->setRoles($um->page);
			$user->setName ($um->getDataUser());			
			return $user;		
		}
	}
	
	/**
	* validate if the username and password is correct
	* @param string username
	* @param string password
	* @return boolean true if validation is sucessful, false otherwise
	*
	*/
	public function validateUser ($username,$password) {		
		$um = new UserManager();
		$um->setUser($username);
		$result = $um->getUser();
        switch ($result['page']) {
            case 'mh' :
                $pass=md5($password);
                if ($result['k_status']=='A') {                                        			
                    $message="Gagal. Silahkan masukan username dan password dengan benar.";
                }else{
                    $message="Mohon maaf status Anda diluar AKTIF. Hubungi Bagian Administrasi.";		
                }
            break;
            case 'd' :
            case 'k' :
            case 'on' :
            case 'sa' :
                $pass=hash('sha256', $result['salt'] . hash('sha256', $password));
                $message="Gagal. Silahkan masukan username dan password dengan benar.";
            break;
            case 'm' :
                if ($result['salt']=='') {
                    $pass=md5($password);
                }else{
                    $pass=hash('sha256', $result['salt'] . hash('sha256', $password));
                }
                $message="Gagal. Silahkan masukan username dan password dengan benar.";
            break;
            default :
                $message="Gagal. Silahkan masukan username dan password dengan benar.";
                $pass=md5($password);
        }    
		if ($result['userpassword']==$pass && $result['active']==1) {
			return true;
        }else{
			throw new Exception ($message);
        }
		
	}	
	/**
	* Dua method dibawah ini tidak dipakai. Tetapi harus tetap Ada.
	*
	*/			
	public function saveUserToCookie($cookie) { }
	
	public function getUserFromCookie($cookie) { }
	
	
}
?>