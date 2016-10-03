<?php
prado::using ('Application.MainPageMHS');
class CDetailPengumuman extends MainPageMHS {
    public $DataDiskusi;
	public function onLoad($param) {		
		parent::onLoad($param);		            
        $this->showPengumuman=true;                     
        $this->createObj('forum');
		if (!$this->IsPostBack&&!$this->IsCallBack) {              
            if (!isset($_SESSION['currentPageDetailPengumuman'])||$_SESSION['currentPageDetailPengumuman']['page_name']!='mh.forum.DetailPengumuman') {                                                                                
                $_SESSION['currentPageDetailPengumuman']=array('page_name'=>'mh.forum.DetailPengumuman','page_num'=>0,'search'=>false,'DataDiskusi'=>array());
            }            
            try {
                $id=addslashes($this->request['id']);
                $str = "SELECT fp.idpost,fp.idkategori,fk.nama_kategori,fp.title,fp.content,fp.nama_user,fp.date_added FROM pengumuman fp LEFT JOIN forumkategori fk ON (fp.idkategori=fk.idkategori)  WHERE fp.idpost=$id";        
                $this->DB->setFieldTable (array('idpost','idkategori','nama_kategori','title','content','nama_user','date_added'));			
                $r=$this->DB->getRecord($str);	
                if (isset($r[1])) {                              
                    $str="UPDATE pengumuman SET unread=0 WHERE idpost=$id";
                    $this->DB->updateRecord($str);
                    
                    $this->DataDiskusi=$r[1];
                    $_SESSION['currentPageDetailPengumuman']['DataDiskusi']=$r[1];                    
                    $this->populateData();
                }
            } catch (Exception $ex) {
                $this->idProcess='view';
                $_SESSION['currentPageDetailPengumuman']['DataDiskusi']=array();
            }            
		}                
	}  
    public function populateData () {
        $id=$_SESSION['currentPageDetailPengumuman']['DataDiskusi']['idpost'];           
        $str = "SELECT idpost,title,content,nama_user,date_added FROM pengumuman fp WHERE parentpost=$id ORDER BY date_added";        
        $this->DB->setFieldTable (array('idpost','title','content','nama_user','date_added'));			
        $r=$this->DB->getRecord($str);	
        $result=array();
        while (list($k,$v)=each($r)) {            
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
        $this->RepeaterS->dataBind();		    

    }    
    public function kirimKonten ($sender,$param) {
		if ($this->IsValid) {	
            $idpost=$_SESSION['currentPageDetailPengumuman']['DataDiskusi']['idpost'];            
            $idkategori=$_SESSION['currentPageDetailPengumuman']['DataDiskusi']['idkategori'];
            $judul=$_SESSION['currentPageDetailPengumuman']['DataDiskusi']['title'];
            $content = strip_tags(addslashes($this->txtAddContent->Text));
            $this->txtAddContent->Text='';
            $userid=$this->Pengguna->getDataUser('userid');                        
            $nama_user=$this->Pengguna->getDataUser('username');                        
            $str = "INSERT INTO pengumuman (idpost,idkategori,parentpost,title,content,userid,tipe,nama_user,date_added) VALUES (NULL,$idkategori,$idpost,'$judul','$content',$userid,'m','$nama_user',NOW())";                   
            $this->DB->insertRecord($str);
            $this->populateData();
        }
    }
}
?>