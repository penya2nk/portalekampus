<?php
prado::using ('Application.MainPageMHS');
class CDulangMHSLama Extends MainPageMHS {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikDulang=true;
        $this->showDulangMHSLama=true;                
        $this->createObj('Finance');
        $this->createObj('KRS');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageDulangMHSLama'])||$_SESSION['currentPageDulangMHSLama']['page_name']!='mh.riwayat.DulangMHSLama') {
				$_SESSION['currentPageDulangMHSLama']=array('page_name'=>'mh.riwayat.DulangMHSLama','page_num'=>0,'search'=>false);												
			}
            $_SESSION['currentPageDulangMHSLama']['search']=false;
        
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();

            $this->populateData();
            
		}	
	}
    public function populateData() {
        $this->KRS->setDataMHS($this->Pengguna->getDataUser());
        $nim=$this->Pengguna->getDataUser('nim');
        $str = "SELECT d.iddulang,d.tahun,d.idsmt,d.tanggal,d.idkelas,k.nkelas,d.k_status,sm.n_status FROM dulang d LEFT JOIN kelas k ON (d.idkelas=k.idkelas) LEFT JOIN status_mhs sm ON (d.k_status=sm.k_status) WHERE nim='$nim' ORDER BY d.iddulang DESC";				        
		$this->DB->setFieldTable(array('iddulang','tahun','idsmt','tanggal','idkelas','nkelas','k_status','n_status'));
		$r=$this->DB->getRecord($str);                
        $result=array();
        while(list($k,$v)=each($r)) {
            $v['tanggal']=$v['tanggal'] == '0000-00-00 00:00:00' ? '-' :$this->TGL->tanggal('l, d F Y',$v['tanggal']);
            $isikrs='tidak isi';
            if ($v['k_status']=='A') {
                $this->KRS->getDataKRS($v['tahun'],$v['idsmt']);  
                $datakrs=$this->KRS->DataKRS;
                $isikrs='belum isi';
                if (isset($datakrs['idkrs'])) {
                    $isikrs=$this->KRS->DataKRS['sah']==true ? 'sudah isi [sah]':'sudah isi [belum disahkan]';
                }                
            }
            $v['tahun']=$this->DMaster->getNamaTA($v['tahun']);		
            $v['semester'] = $this->setup->getSemester($v['idsmt']);
            $v['kelas']=$v['nkelas'];
            $v['status']=$v['n_status'];
            
            
            $v['isikrs']=$isikrs;
            $result[$k]=$v;
        }
		$this->RepeaterDulang->DataSource=$result;
		$this->RepeaterDulang->dataBind();
    }
}
?>