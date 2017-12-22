<?php
prado::using ('Application.MainPageD');
class CDetailDiskusi extends MainPageD {
    public $DataDiskusi;
	public function onLoad($param) {		
		parent::onLoad($param);		            
        $this->showForumDiskusi=true;                     
        $this->createObj('forum');
		if (!$this->IsPostBack&&!$this->IsCallBack) {              
            if (!isset($_SESSION['currentPageDetailDiskusi'])||$_SESSION['currentPageDetailDiskusi']['page_name']!='d.forum.DetailDiskusi') {                                                                                
                $_SESSION['currentPageDetailDiskusi']=array('page_name'=>'d.forum.DetailDiskusi','page_num'=>0,'search'=>false,'DataDiskusi'=>array());
            }            
            try {
                $id=addslashes($this->request['id']);
                $str = "SELECT fp.idpost,fp.idkategori,fk.nama_kategori,fp.title,fp.content,fp.nama_user,fp.date_added FROM forumposts fp LEFT JOIN forumkategori fk ON (fp.idkategori=fk.idkategori)  WHERE fp.idpost=$id";        
                $this->DB->setFieldTable (array('idpost','idkategori','nama_kategori','title','content','nama_user','date_added'));			
                $r=$this->DB->getRecord($str);	
                if (isset($r[1])) {                              
                    $str="UPDATE forumposts SET unread=0 WHERE idpost=$id";
                    $this->DB->updateRecord($str);
                    
                    $this->DataDiskusi=$r[1];
                    $_SESSION['currentPageDetailDiskusi']['DataDiskusi']=$r[1];                    
                    $this->populateData();
                }
            } catch (Exception $ex) {
                $this->idProcess='view';
                $_SESSION['currentPageDetailDiskusi']['DataDiskusi']=array();
            }            
		}                
	}  
    public function populateData () {
        $id=$_SESSION['currentPageDetailDiskusi']['DataDiskusi']['idpost'];           
        $str = "SELECT idpost,title,content,nama_user,date_added FROM forumposts fp WHERE parentpost=$id ORDER BY date_added";        
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
            $idpost=$_SESSION['currentPageDetailDiskusi']['DataDiskusi']['idpost'];            
            $idkategori=$_SESSION['currentPageDetailDiskusi']['DataDiskusi']['idkategori'];
            $judul=$_SESSION['currentPageDetailDiskusi']['DataDiskusi']['title'];
            $content = strip_tags(addslashes($this->txtAddContent->Text));
            $this->txtAddContent->Text='';
            $userid=$this->Pengguna->getDataUser('userid');                        
            $nama_user=$this->Pengguna->getDataUser('username');                        
            $str = "INSERT INTO forumposts (idpost,idkategori,parentpost,title,content,userid,tipe,nama_user,date_added) VALUES (NULL,$idkategori,$idpost,'$judul','$content',$userid,'m','$nama_user',NOW())";                   
            $this->DB->insertRecord($str);
            $this->populateData();
        }
    }
}