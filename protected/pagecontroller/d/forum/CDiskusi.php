<?php
prado::using ('Application.MainPageD');
class CDiskusi extends MainPageD {        
	public function onLoad($param) {		
		parent::onLoad($param);		            
        $this->showForumDiskusi=true;                     
        $this->createObj('forum');
		if (!$this->IsPostBack&&!$this->IsCallBack) {              
            if (!isset($_SESSION['currentPageDiskusi'])||$_SESSION['currentPageDiskusi']['page_name']!='d.forum.Diskusi') {                                                                                
                $_SESSION['currentPageDiskusi']=array('page_name'=>'d.forum.Diskusi','page_num'=>0,'page_num_unread'=>0,'search'=>false,'activeviewindex'=>0);
            }
            $this->MVMenuForum->ActiveViewIndex=$_SESSION['currentPageDiskusi']['activeviewindex']; 
		}                
	}  
    public function changeView ($sender,$param) {                
        $activeview = $_SESSION['currentPageDiskusi']['activeviewindex'];                
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
            $_SESSION['currentPageDiskusi']['activeviewindex']=$this->MVMenuForum->ActiveViewIndex;
            $this->redirect('forum.Diskusi',true);
        }        
    }
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageDiskusi']['page_num']=$param->NewPageIndex;
		$this->populateNewsFeed($_SESSION['currentPageDiskusi']['search']);
	}
    public function populateNewsFeed ($search=false) {
        if ($search) {  
            
        }else{
            $str = "SELECT fp.idpost,fp.userid,fk.nama_kategori,fp.title,fp.content,fp.userid,fp.nama_user,fp.tipe,fp.date_added FROM forumposts fp, forumkategori fk WHERE fp.idkategori=fk.idkategori AND parentpost=0";
            $jumlah_baris=$this->DB->getCountRowsOfTable("forumposts WHERE parentpost=0",'idpost');						
        }        
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDiskusi']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageDiskusi']['page_num']=0;}
        $str="$str ORDER BY date_added DESC LIMIT $offset,$limit";				
		$this->DB->setFieldTable (array('idpost','userid','nama_kategori','title','content','nama_user','tipe','date_added'));			
		$r=$this->DB->getRecord($str);	
        $result=array();
        while (list($k,$v)=each($r)) {
            $idpost=$v['idpost'];
            $userid=$v['userid'];
            $photo='resources/userimages/no_photo.png';
            switch ($v['tipe']) {
                case 'mh' :   
                    $str = "SELECT photo_profile FROM profiles_mahasiswa WHERE nim='$userid'";
                    $this->DB->setFieldTable (array('photo_profile'));			
                    $profile=$this->DB->getRecord($str);	
                    $photo=$profile[1]['photo_profile'];
                    $urlprofiluser=$this->constructUrl('kemahasiswaan.ProfilMahasiswa',true,array('id'=>$v['userid']));
                break;
                case 'm' :
                    $str = "SELECT foto AS photo_profile FROM user WHERE userid='$userid'";
                    $this->DB->setFieldTable (array('photo_profile'));			
                    $profile=$this->DB->getRecord($str);	
                    $photo=$profile[1]['photo_profile'];
                    $urlprofiluser='#';
                break;
                default :
                    $urlprofiluser='#';
            }
            $v['urlprofiluser']=$urlprofiluser;
            $v['photo_profile']=$photo;
            $v['jumlahcomment']=$this->DB->getCountRowsOfTable("forumposts WHERE parentpost=$idpost",'idpost');
            $v['tanggal_post']=$this->page->TGL->relativeTime(date('Y-m-d H:i:s'),$v['date_added'],'lasttweet');
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
        
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
    }    
    public function populateUnread ($search=false) {
        if ($search) {  
            
        }else{
            $str = "SELECT fp.idpost,fk.nama_kategori,fp.title,fp.content,fp.nama_user,fp.date_added FROM forumposts fp, forumkategori fk WHERE fp.idkategori=fk.idkategori AND parentpost=0 AND unread=1";
            $jumlah_baris=$this->DB->getCountRowsOfTable("forumposts WHERE parentpost=0 AND unread=1",'idpost');						
        }        
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDiskusi']['page_num_unread'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageDiskusi']['page_num_unread']=0;}
        $str="$str ORDER BY date_added DESC LIMIT $offset,$limit";				
		$this->DB->setFieldTable (array('idpost','nama_kategori','title','content','nama_user','date_added'));			
		$r=$this->DB->getRecord($str);	
        $result=array();
        while (list($k,$v)=each($r)) {
            $idpost=$v['idpost'];
            $v['jumlahcomment']=$this->DB->getCountRowsOfTable("forumposts WHERE parentpost=$idpost",'idpost');
            $v['tanggal_post']=$this->page->TGL->relativeTime(date('Y-m-d H:i:s'),$v['date_added'],'lasttweet');
            $result[$k]=$v;
        }
		$this->RepeaterUnread->DataSource=$result;
		$this->RepeaterUnread->dataBind();
        
        $this->paginationInfo2->Text=$this->getInfoPaging($this->RepeaterS);
    }
    public function kirimKonten ($sender,$param) {
		if ($this->IsValid) {	
            $idkategori = addslashes($this->cmbAddKategori->Text);
            $judul = strip_tags(addslashes($this->txtAddTitle->Text));
            $content = strip_tags(addslashes($this->txtAddContent->Text));
            $userid=$this->Pengguna->getDataUser('userid');                        
            $nama_user=$this->Pengguna->getDataUser('username');                        
            $str = "INSERT INTO forumposts (idpost,idkategori,title,content,userid,tipe,nama_user,date_added) VALUES (NULL,$idkategori,'$judul','$content',$userid,'d','$nama_user',NOW())";
            $this->DB->insertRecord($str);
            $_SESSION['currentPageDiskusi']['activeviewindex']=0;
            $this->redirect('forum.Diskusi', true);
            
        }
    }
    public function setUnreadFalse ($sender,$param) {
        $id=$this->getDataKeyField($sender,$this->RepeaterUnread);        
        $str="UPDATE forumposts SET unread=0 WHERE idpost=$id";
        $this->DB->updateRecord($str);
        $this->redirect('forum.DetailDiskusi', true, array('id'=>$id));
    }
}