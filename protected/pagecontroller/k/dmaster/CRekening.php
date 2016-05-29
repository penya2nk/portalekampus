<?php
prado::using ('Application.MainPageK');
class CRekening Extends MainPageK {
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showDMaster=true;
        $this->showRekening=true;
		if (!$this->IsPostBack&&!$this->IsCallback) {
			$this->populateData();
		}
	}	
	protected function populateData() {		
		$str = 'SELECT idkombi,nama_kombi,periode_pembayaran FROM kombi ORDER BY  periode_pembayaran,nama_kombi ASC';
		$this->DB->setFieldTable(array('idkombi','nama_kombi','periode_pembayaran'));
		$result = $this->DB->getRecord($str);
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
	}	
}

?>