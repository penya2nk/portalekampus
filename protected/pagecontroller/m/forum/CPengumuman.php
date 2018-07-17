<?php
prado::using ('Application.MainPageM');
class CPengumuman extends MainPageM {        
	public function onLoad($param) {		
		parent::onLoad($param);		            
        $this->showPengumuman=true;                     
        $this->createObj('forum');
		if (!$this->IsPostBack&&!$this->IsCallBack) {              
            if (!isset($_SESSION['currentPagePengumuman'])||$_SESSION['currentPagePengumuman']['page_name']!='m.forum.Pengumuman') {                                                                                
                $_SESSION['currentPagePengumuman']=array('page_name'=>'m.forum.Pengumuman','page_num'=>0,'page_num_unread'=>0,'search'=>false,'activeviewindex'=>0);
            }
            $this->MVMenuForum->ActiveViewIndex=$_SESSION['currentPagePengumuman']['activeviewindex']; 
		}                
	}  
    public function changeView ($sender,$param) {                
        $activeview = $_SESSION['currentPagePengumuman']['activeviewindex'];                
        if ($activeview == $this->MVMenuForum->ActiveViewIndex) {
            switch ($activeview) {
                case 0 : //diskusi newsfeed
                    $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');
                    $this->populateNewsFeed();
                break;
                case 1 :
                    $this->cmbAddKategori->DataSource=$this->Forum->getListForumKategori();
                    $this->cmbAddKategori->DataBind(); 
                break;
                case 2 : //diskusi unread
                    $this->RepeaterUnread->PageSize=$this->setup->getSettingValue('default_pagesize');
                    $this->populateUnread();
                break;
            }
        }else{
            $_SESSION['currentPagePengumuman']['activeviewindex']=$this->MVMenuForum->ActiveViewIndex;
            $this->redirect('forum.Pengumuman',true);
        }        
    }
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePengumuman']['page_num']=$param->NewPageIndex;
		$this->populateNewsFeed($_SESSION['currentPagePengumuman']['search']);
	}
    public function populateNewsFeed ($search=false) {
        if ($search) {  
            
        }else{
            $str = "SELECT fp.idpost,fp.userid,fk.nama_kategori,fp.title,fp.content,fp.nama_user,fp.tipe,fp.date_added FROM pengumuman fp, forumkategori fk WHERE fp.idkategori=fk.idkategori AND parentpost=0";
            $jumlah_baris=$this->DB->getCountRowsOfTable("pengumuman WHERE parentpost=0",'idpost');						
        }        
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePengumuman']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPagePengumuman']['page_num']=0;}
        $str="$str ORDER BY date_added DESC LIMIT $offset,$limit";				
		$this->DB->setFieldTable (array('idpost','userid','nama_kategori','title','content','nama_user','tipe','date_added'));			
		$r=$this->DB->getRecord($str);	
        $result=array();
        while (list($k,$v)=each($r)) {
            $idpost=$v['idpost'];
            switch ($v['tipe']) {
                case 'mh' :                    
                    $urlprofiluser=$this->constructUrl('kemahasiswaan.ProfilMahasiswa',true,array('id'=>$v['userid']));
                break;
                default :
                    $urlprofiluser='#';
            }
            $v['urlprofiluser']=$urlprofiluser;
            $v['jumlahcomment']=$this->DB->getCountRowsOfTable("pengumuman WHERE parentpost=$idpost",'idpost');
            $v['tanggal_post']=$this->page->TGL->tanggal('l, d F Y H:i',$v['date_added']);
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
        
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
    }    
    public function populateUnread ($search=false) {
        if ($search) {  
            
        }else{
            $str = "SELECT fp.idpost,fk.nama_kategori,fp.title,fp.content,fp.userid,fp.nama_user,fp.date_added FROM pengumuman fp, forumkategori fk WHERE fp.idkategori=fk.idkategori AND parentpost=0 AND unread=1";
            $jumlah_baris=$this->DB->getCountRowsOfTable("pengumuman WHERE parentpost=0 AND unread=1",'idpost');						
        }        
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePengumuman']['page_num_unread'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPagePengumuman']['page_num_unread']=0;}
        $str="$str ORDER BY date_added DESC LIMIT $offset,$limit";				
		$this->DB->setFieldTable (array('idpost','nama_kategori','title','content','nama_user','date_added'));			
		$r=$this->DB->getRecord($str);	
        $result=array();
        while (list($k,$v)=each($r)) {
            $idpost=$v['idpost'];
            $v['jumlahcomment']=$this->DB->getCountRowsOfTable("pengumuman WHERE parentpost=$idpost",'idpost');
            $v['tanggal_post']=$this->page->TGL->relativeTime(date('Y-m-d H:i:s'),$v['date_added'],'lasttweet');
            $result[$k]=$v;
        }
		$this->RepeaterUnread->DataSource=$result;
		$this->RepeaterUnread->dataBind();
        
        $this->paginationInfo2->Text=$this->getInfoPaging($this->RepeaterS);
    }    
    public function filepengumumanUploaded ($sender,$param) {
        if($sender->HasFile) {
            $file_name=$sender->FileName;
            $file_size=$sender->FileSize;
            $file_type=$sender->FileType;
            $real_file_name=$this->setup->cleanFileNameString($file_name);
            $file_path=BASEPATH."resources/files/$real_file_name";
            $file_path_temp=BASEPATH."resources/tmp/$real_file_name";
            $file_url="resources/files/$real_file_name";
            
            $this->hiddenfile_name->Value=addslashes($file_name);
            $this->hiddenfile_type->Value=addslashes($file_type);
            $this->hiddenfile_size->Value=addslashes($file_size);
            $this->hiddenfile_path->Value=addslashes($file_path);
            $this->hiddenfile_path_temp->Value=addslashes($file_path_temp);
            $this->hiddenfile_url->Value=addslashes($file_url);
            
            $sender->saveAs($file_path_temp);
        }            
    }
    public function kirimKonten ($sender,$param) {
		if ($this->IsValid) {	
            $idkategori = addslashes($this->cmbAddKategori->Text);
            $judul = strip_tags(addslashes($this->txtAddTitle->Text));
            $content = strip_tags(addslashes($this->txtAddContent->Text));
            $userid=$this->Pengguna->getDataUser('userid');                        
            $nama_user=$this->Pengguna->getDataUser('username'); 
            
			$file_name=$this->hiddenfile_name->Value;
			$file_type=$this->hiddenfile_type->Value;
			$file_size=$this->hiddenfile_size->Value;
			$file_path=$this->hiddenfile_path->Value;
			$file_path_temp=$this->hiddenfile_path_temp->Value;
			$file_url=$this->hiddenfile_url->Value;
			if (file_exists($file_path_temp)) {
				rename ($file_path_temp,$file_path);
			}
            $str = "INSERT INTO pengumuman SET idpost=NULL,idkategori=$idkategori,title='$judul',content='$content',userid=$userid,tipe='m',nama_user='$nama_user',file_name='$file_name',file_type='$file_type',file_size='$file_size',file_path='$file_path',file_url='$file_url',date_added=NOW()";
            $this->DB->insertRecord($str);
            $_SESSION['currentPagePengumuman']['activeviewindex']=0;
            $this->redirect('forum.Pengumuman', true);
            
        }
    }
    public function setUnreadFalse ($sender,$param) {
        $id=$this->getDataKeyField($sender,$this->RepeaterUnread);        
        $str="UPDATE pengumuman SET unread=0 WHERE idpost=$id";
        $this->DB->updateRecord($str);
        $this->redirect('forum.DetailPengumuman', true, array('id'=>$id));
    }
}