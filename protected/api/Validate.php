<?php
prado::using('Application.api.BaseWS');
class Validate extends BaseWS {
	private function checkUsernamePassword() {
		$username=addslashes($this->request['username']);
		$password=addslashes($this->request['userpassword']);
		
		return array();
	}
	public function getJsonContent() {
		$data = $this->checkUsernamePassword();
		return $data;
	}
}