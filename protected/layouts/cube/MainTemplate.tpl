<!DOCTYPE html>
<html>
<com:THead>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->baseUrl%>/css/bootstrap/bootstrap.min.css" />	
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->baseUrl%>/css/libs/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->baseUrl%>/css/libs/nanoscroller.css" />
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->baseUrl%>/css/compiled/theme_styles.css" />
    <com:TContentPlaceHolder ID="csscontent" />   		
	<link type="image/x-icon" href="resources/favicon.ico" rel="shortcut icon"/>
	<!--<link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'>-->
	<!--[if lt IE 9]>
		<script src="<%=$this->Page->Theme->baseUrl%>/js/html5shiv.js"></script>
		<script src="<%=$this->Page->Theme->baseUrl%>/js/respond.min.js"></script>
	<![endif]-->
</com:THead>
<body class="theme-blue fixed-header">
<com:TForm id="mainform">
	<div id="theme-wrapper">
		<header class="navbar" id="header-navbar">
			<div class="container">
                <a href="<%=$this->Page->constructUrl('Home',true)%>" id="logo" class="navbar-brand">
					Portal E-Kampus
                    <!--<img src="<%=$this->Page->Theme->baseUrl%>/img/logo.png" alt="" class="normal-logo logo-white"/>-->					
				</a>				
				<div class="clearfix">
				<button class="navbar-toggle" data-target=".navbar-ex1-collapse" data-toggle="collapse" type="button">
					<span class="sr-only">Toggle navigation</span>
					<span class="fa fa-bars"></span>
				</button>				
				<div class="nav-no-collapse navbar-left pull-left hidden-sm hidden-xs">
					<ul class="nav navbar-nav pull-left">
						<li>
							<a class="btn" id="make-small-nav">
								<i class="fa fa-bars"></i>
							</a>
						</li>
                        <li class="dropdown hidden-xs visible">
							<a class="btn dropdown-toggle" data-toggle="dropdown">
								Themes
								<i class="fa fa-caret-down"></i>
							</a>
                            <com:TRepeater ID="RepeaterTheme" DataKeyField="idtheme">
                                <prop:HeaderTemplate>
                                    <ul class="dropdown-menu">
                                </prop:HeaderTemplate>
                                <prop:ItemTemplate>
                                    <li class="item">
                                    <com:TActiveLinkButton ID="btnChangeTheme" ClientSide.PostState="false" OnClick="SourceTemplateControl.changeTheme" Enabled="<%#$_SESSION['theme']!=$this->DataItem['idtheme']%>" CommandParameter="<%#$this->DataItem['idtheme']%>">
                                            <prop:Text>
                                                <%#$_SESSION['theme']==$this->DataItem['idtheme'] ? '<i class="fa fa-check-square"></i>' :''%>
                                                <%#$this->DataItem['namatheme']%>
                                            </prop:Text>
                                            <prop:ClientSide.OnPreDispatch>
                                                $('loading').show();                                             
                                                $('<%=$this->btnChangeTheme->ClientId%>').disabled='disabled';						
                                            </prop:ClientSide.OnPreDispatch>
                                            <prop:ClientSide.OnLoading>
                                                $('<%=$this->btnChangeTheme->ClientId%>').disabled='disabled';									                            
                                            </prop:ClientSide.OnLoading>
                                            <prop:ClientSide.onComplete>
                                                $('loading').hide();                                                
                                            </prop:ClientSide.OnComplete>
                                        </com:TActiveLinkButton>                                        
                                    </li>
                                </prop:ItemTemplate>
                                <prop:FooterTemplate>
                                    </ul>
                                </prop:FooterTemplate>
                            </com:TRepeater>
						</li>
					</ul>
				</div>				
				<div class="nav-no-collapse pull-right" id="header-nav">
					<ul class="nav navbar-nav pull-right">	
                        <li>                            
                            <div id="loading" style="display: none">
                                Please wait while process your request !!!
                            </div>
                        </li>                        
                        <li class="hidden-xxs">
							<com:THyperLink ID="linkTopTASemester" CssClass="btn">
								Saat ini Anda berada di T.A <%=$this->Page->setup->getSettingValue('default_ta');%>/<%=$this->Page->setup->getSemester($this->Page->setup->getSettingValue('default_semester'));%>
							</com:THyperLink>
						</li>
						<li class="dropdown profile-dropdown visible">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<img alt="<%=$this->Page->Pengguna->getUsername()%>" src="resources/userimages/<%=$_SESSION['foto']%>" OnError="no_photo(this,'<%=$this->Page->setup->getAddress()%>/resources/userimages/no_photo.png')" />
								<span class="hidden-xs"><%=$this->Page->Pengguna->getUsername()%></span> <b class="caret"></b>
							</a>
							<ul class="dropdown-menu dropdown-menu-right">
								<li><a href="<%=$this->Page->constructUrl('settings.Profiles',true)%>"><i class="fa fa-user"></i>Profiles</a></li>
                                <li><a href="<%=$this->Page->constructUrl('Logout')%>"><i class="fa fa-power-off"></i>Logout</a></li>
							</ul>
						</li>                        
						<li class="hidden-xxs">
							<a class="btn" href="<%=$this->Page->constructUrl('Logout')%>">
								<i class="fa fa-power-off"></i>
							</a>
						</li>
					</ul>
				</div>
				</div>
			</div>
		</header>
		<div id="page-wrapper" class="container">
			<div class="row">
				<div id="nav-col">
					<section id="col-left" class="col-left-nano">
						<div id="col-left-inner" class="col-left-nano-content">
							<div id="user-left-box" class="clearfix hidden-sm hidden-xs dropdown profile2-dropdown">
								<img alt="<%=$this->Page->Pengguna->getUsername()%>" src="resources/userimages/<%=$_SESSION['foto']%>" />
								<div class="user-box">
									<span class="name">
										<a href="#" class="dropdown-toggle" data-toggle="dropdown">
											<%=$this->Page->Pengguna->getUsername()%>
											<i class="fa fa-angle-down"></i>
										</a>
										<ul class="dropdown-menu">
											<li><a href="<%=$this->Page->constructUrl('settings.Profiles',true)%>"><i class="fa fa-user"></i>Profiles</a></li>
                                            <li><a href="<%=$this->Page->constructUrl('Logout')%>"><i class="fa fa-power-off"></i>Logout</a></li>
										</ul>
									</span>
                                    <com:TActiveLabel ID="lblStatusUser" CssClass="status" />									
								</div>
							</div>
							<div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav">	
								<ul class="nav nav-pills nav-stacked">
									<li class="nav-header nav-header-first hidden-sm hidden-xs">
										NAVIGASI
									</li>
                                    <com:TLiteral Visible="<%=$this->Page->Pengguna->getTipeUser()=='m'%>">
                                        <li<%=$this->Page->showDashboard==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('Home',true)%>">
                                                <i class="fa fa-dashboard"></i>
                                                <span>Dashboard</span>											
                                            </a>                                        
                                        </li>  
                                        <li<%=$this->Page->showForumDiskusi==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('forum.Diskusi',true)%>">
                                                <i class="fa fa-comment"></i>
                                                <span>Forum Diskusi</span>											
                                            </a>                                        
                                        </li>  
                                        <li class="nav-header nav-header-first hidden-sm hidden-xs">
                                            DATA MASTER
                                        </li>
                                        <li<%=$this->Page->showMatakuliah==true ? ' class="active" ':''%>>                                            
                                            <a href="<%=$this->Page->constructUrl('dmaster.Matakuliah',true)%>">
                                                <i class="fa fa-bars"></i>
                                                <span>Matakuliah</span>                                                
                                            </a>
                                        </li>
                                        <li<%=$this->Page->showSubMenuKuesioner==true?' class="active"':''%>>
                                            <a href="#" class="dropdown-toggle">
                                                <i class="fa fa-sign-in"></i>
                                                <span>Kuesioner</span>
                                                <i class="fa fa-angle-right drop-icon"></i>
                                            </a>
                                            <ul class="submenu">
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('dmaster.KelompokPertanyaan',true)%>" <%=$this->Page->showKelompokPertanyaan==true ? ' class="active" ':''%>>
                                                        Kelompok Pertanyaan
                                                    </a>
                                                </li>                                                
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('dmaster.DaftarPertanyaan',true)%>" <%=$this->Page->showDaftarPertanyaan==true ? ' class="active" ':''%>>
                                                        Daftar Pertanyaan
                                                    </a>
                                                </li>                                                
                                            </ul>
                                        </li>    
                                        <li class="nav-header nav-header-first hidden-sm hidden-xs">
                                            SPMB
                                        </li>
                                        <li<%=$this->Page->showKonversiMatakuliah==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('spmb.KonversiMatakuliah',true)%>">
                                                <i class="fa fa-share-alt"></i>
                                                <span>Konversi Matakuliah</span>											
                                            </a>                                        
                                        </li>  
                                        <li<%=$this->Page->showSubMenuSPMBPendaftaran==true?' class="active"':''%>>
                                            <a href="#" class="dropdown-toggle">
                                                <i class="fa fa-sign-in"></i>
                                                <span>Pendaftaran</span>
                                                <i class="fa fa-angle-right drop-icon"></i>
                                            </a>
                                            <ul class="submenu">
                                                <li>
                                                    <a href="#" <%=$this->Page->showPendaftaranViaFO==true ? ' class="active" ':''%>>
                                                        Via Front Office
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('spmb.PendaftaranViaWeb',true)%>" <%=$this->Page->showPendaftaranViaWeb==true ? ' class="active" ':''%>>
                                                        Via Web
                                                    </a>
                                                </li>                                                
                                            </ul>
                                        </li>    
                                        <li<%=$this->Page->showSubMenuSPMBUjianPMB==true?' class="active"':''%>>
                                            <a href="#" class="dropdown-toggle">
                                                <i class="fa fa-flag-checkered"></i>
                                                <span>Ujian PMB</span>
                                                <i class="fa fa-angle-right drop-icon"></i>
                                            </a>
                                            <ul class="submenu">                                                
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('spmb.PassingGrade',true)%>" <%=$this->Page->showPassingGradePMB==true ? ' class="active" ':''%>>
                                                        Passing Grade
                                                    </a>
                                                </li>                                                
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('spmb.NilaiUjian',true)%>" <%=$this->Page->showNilaiUjianPMB==true ? ' class="active" ':''%>>
                                                        Nilai Ujian
                                                    </a>
                                                </li>                                                
                                            </ul>
                                        </li>    
                                        <li class="nav-header nav-header-first hidden-sm hidden-xs">
                                            AKADEMIK
                                        </li>
                                        <li<%=$this->Page->showSubMenuAkademikKemahasiswaan==true?' class="active"':''%>>
                                            <a href="#" class="dropdown-toggle">
                                                <i class="fa fa-users"></i>
                                                <span>Kemahasiswaan</span>
                                                <i class="fa fa-angle-right drop-icon"></i>
                                            </a>
                                            <ul class="submenu">
                                                <li>
                                                    <a href="#" <%=$this->Page->showProfilMahasiswa==true ? ' class="active" ':''%>>
                                                        Profil Mahasiswa
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('kemahasiswaan.DaftarMahasiswa',true)%>" <%=$this->Page->showDaftarMahasiswa==true ? ' class="active" ':''%>>
                                                        Daftar Mahasiswa
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('kemahasiswaan.PendaftaranKonsentrasi',true)%>" <%=$this->Page->showPendaftaranKonsentrasi==true ? ' class="active" ':''%>>
                                                        Pend. Konsentrasi
                                                    </a>
                                                </li>                                                
                                            </ul>
                                        </li>                                                                                                                       
                                        <li<%=$this->Page->showSubMenuAkademikDulang==true?' class="active"':''%>>
                                            <a href="#" class="dropdown-toggle">
                                                <i class="fa fa-location-arrow"></i>
                                                <span>Daftar Ulang</span>
                                                <i class="fa fa-angle-right drop-icon"></i>
                                            </a>
                                            <ul class="submenu">                                                
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('dulang.DulangMHSBaru',true)%>" <%=$this->Page->showDulangMHSBaru==true ? ' class="active" ':''%>>
                                                        Mahasiswa Baru
                                                    </a>
                                                </li>                                                                                             
                                            </ul>
                                        </li>                                                                                                                       
                                        <li<%=$this->Page->showSubMenuAkademikPerkuliahan==true?' class="active"':''%>>
                                            <a href="#" class="dropdown-toggle">
                                                <i class="fa fa-truck"></i>
                                                <span>Perkuliahan</span>
                                                <i class="fa fa-angle-right drop-icon"></i>
                                            </a>
                                            <ul class="submenu">
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('perkuliahan.Penyelenggaraan',true)%>" <%=$this->Page->showPenyelenggaraan==true ? ' class="active" ':''%>>
                                                        Penyelenggaraan
                                                    </a>
                                                </li>   
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('perkuliahan.KRS',true)%>" <%=$this->Page->showKRS==true ? ' class="active" ':''%>>
                                                        KRS
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('perkuliahan.KRSEkstension',true)%>" <%=$this->Page->showKRSEkstension==true ? ' class="active" ':''%>>
                                                        KRS Ekstension
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('perkuliahan.PesertaMatakuliah',true)%>" <%=$this->Page->showPesertaMatakuliah==true ? ' class="active" ':''%>>
                                                        Peserta Matakuliah
                                                    </a>
                                                </li>  
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('perkuliahan.Kuesioner',true)%>" <%=$this->Page->showKuesioner==true ? ' class="active" ':''%>>
                                                        Kuesioner
                                                    </a>
                                                </li> 
                                            </ul>
                                        </li>   
                                        <li<%=$this->Page->showSubMenuAkademikNilai==true?' class="active"':''%>>
                                            <a href="#" class="dropdown-toggle">
                                                <i class="fa fa-file-excel-o"></i>
                                                <span>Nilai</span>
                                                <i class="fa fa-angle-right drop-icon"></i>
                                            </a>
                                            <ul class="submenu">
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('nilai.DPNA',true)%>" <%=$this->Page->showDPNA==true ? ' class="active" ':''%>>
                                                        DPNA
                                                    </a>
                                                </li>                                                
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('nilai.KHS',true)%>" <%=$this->Page->showKHS==true ? ' class="active" ':''%>>
                                                        KHS
                                                    </a>
                                                </li>                                                
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('nilai.KHSEkstension',true)%>" <%=$this->Page->showKHSEkstension==true ? ' class="active" ':''%>>
                                                        KHS Ekstension
                                                    </a>
                                                </li>                                                
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('nilai.TranskripSementara',true)%>" <%=$this->Page->showTranskripSementara==true ? ' class="active" ':''%>>
                                                        Transkrip Sementara
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('nilai.TranskripKRS',true)%>" <%=$this->Page->showTranskripKRS==true ? ' class="active" ':''%>>
                                                        Transkrip KRS
                                                    </a>
                                                </li>                                                   
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('nilai.TranskripAsli',true)%>" <%=$this->Page->showTranskripAsli==true ? ' class="active" ':''%>>
                                                        Transkrip Asli
                                                    </a>
                                                </li>                                                   
                                            </ul>
                                        </li>   
                                        <li class="nav-header nav-header-first hidden-sm hidden-xs">
                                            SETTING
                                        </li>
                                        <li<%=$this->Page->showVariable==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('settings.Variables',true)%>">
                                                <i class="fa fa-legal"></i>
                                                <span>Variables</span>											
                                            </a>                                        
                                        </li> 
                                        <li<%=$this->Page->showSubMenuSettingAkademik==true?' class="active"':''%>>
                                            <a href="#" class="dropdown-toggle">
                                                <i class="fa fa-medkit"></i>
                                                <span>Akademik</span>
                                                <i class="fa fa-angle-right drop-icon"></i>
                                            </a>
                                            <ul class="submenu">

                                            </ul>
                                        </li>
                                        <li<%=$this->Page->showSubMenuSettingSistem==true?' class="active"':''%>>
                                            <a href="#" class="dropdown-toggle">
                                                <i class="fa fa-cog"></i>
                                                <span>Sistem</span>
                                                <i class="fa fa-angle-right drop-icon"></i>
                                            </a>
                                            <ul class="submenu">
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('settings.UserManajemen',true)%>" <%=$this->Page->showUserManajemen==true ? ' class="active" ':''%>>
                                                        User Manajemen
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('settings.UserManajemen',true)%>" <%=$this->Page->showUserDosen==true ? ' class="active" ':''%>>
                                                        User Dosen
                                                    </a>
                                                </li>
                                                <li>                                                
                                                    <a href="<%=$this->Page->constructUrl('settings.Cache',true)%>"<%=$this->Page->showCache==true?' class="active"':''%>>                                                    
                                                        Cache											
                                                    </a>                                        
                                                </li>
                                            </ul>
                                        </li> 
                                        <li<%=$this->Page->showProfiles==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('settings.Profiles',true)%>">
                                                <i class="fa fa-user"></i>
                                                <span>Profiles</span>											
                                            </a>                                        
                                        </li> 
                                        <li>
                                            <a href="<%=$this->Page->setup->getAddress()%>/change_log.txt">
                                                <i class="fa fa-file-o"></i>
                                                <span>Change Log</span>											
                                            </a>                                        
                                        </li> 
                                    </com:TLiteral>		
                                    <com:TLiteral Visible="<%=$this->Page->Pengguna->getTipeUser()=='mh'%>">
                                        <li<%=$this->Page->showDashboard==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('Home',true)%>">
                                                <i class="fa fa-dashboard"></i>
                                                <span>Dashboard</span>											
                                            </a>                                        
                                        </li>         
                                        <li<%=$this->Page->showForumDiskusi==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('forum.Diskusi',true)%>">
                                                <i class="fa fa-comment"></i>
                                                <span>Forum Diskusi</span>											
                                            </a>                                        
                                        </li> 
                                        <li class="nav-header nav-header-first hidden-sm hidden-xs">
                                            AKADEMIK
                                        </li>   
                                        <li<%=$this->Page->showSubMenuAkademikPerkuliahan==true?' class="active"':''%>>
                                            <a href="#" class="dropdown-toggle">
                                                <i class="fa fa-truck"></i>
                                                <span>Perkuliahan</span>
                                                <i class="fa fa-angle-right drop-icon"></i>
                                            </a>
                                            <ul class="submenu">                                                                                           
                                                <li>
                                                    <a href="#" <%=$this->Page->showKuesioner==true ? ' class="active" ':''%>>
                                                        Kuesioner
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="<%=$this->Page->constructUrl('perkuliahan.KRS',true)%>" <%=$this->Page->showKRS==true ? ' class="active" ':''%>>
                                                        KRS
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>   
                                        <li<%=$this->Page->showSubMenuAkademikNilai==true?' class="active"':''%>>
                                            <a href="#" class="dropdown-toggle">
                                                <i class="fa fa-file-excel-o"></i>
                                                <span>Nilai</span>
                                                <i class="fa fa-angle-right drop-icon"></i>
                                            </a>
                                            <ul class="submenu">                                                                                   
                                                <li>
                                                    <a<%=$this->Page->showKHS==true ? ' class="active" ':''%>  href="<%=$this->Page->constructUrl('nilai.KHS',true)%>">                                                        
                                                        <span>Kartu Hasil Studi</span>											
                                                    </a> 
                                                </li>                                                
                                                <li>
                                                    <a<%=$this->Page->showTranskripSementara==true ? ' class="active" ':''%>  href="<%=$this->Page->constructUrl('nilai.TranskripSementara',true)%>">                                                        
                                                        <span>Transkrip Sementara</span>											
                                                    </a> 
                                                </li> 
                                                <li>
                                                    <a<%=$this->Page->showTranskripKRS==true ? ' class="active" ':''%>  href="<%=$this->Page->constructUrl('nilai.TranskripKRS',true)%>">                                                        
                                                        <span>Transkrip KRS</span>											
                                                    </a> 
                                                </li> 
                                            </ul>
                                        </li>                                          
                                        <li class="nav-header nav-header-first hidden-sm hidden-xs">
                                            KEMAHASISWAAN
                                        </li>  
                                        <li<%=$this->Page->showDaftarKonsentrasi==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('kemahasiswaan.DaftarKonsentrasi',true)%>">
                                                <i class="fa fa-users"></i>
                                                <span>Daftar Konsentrasi</span>											
                                            </a>                                        
                                        </li>     
                                    </com:TLiteral>	
                                    <com:TLiteral Visible="<%=$this->Page->Pengguna->getTipeUser()=='mb'%>">
                                        <li<%=$this->Page->showDashboard==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('Home',true)%>">
                                                <i class="fa fa-dashboard"></i>
                                                <span>Dashboard</span>											
                                            </a>                                        
                                        </li> 
                                        <li<%=$this->Page->showFormulirPendaftaran==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('FormulirPendaftaran',true)%>">
                                                <i class="fa fa-file-o"></i>
                                                <span>Formulir Pendaftaran</span>											
                                            </a>                                        
                                        </li>
                                        <li<%=$this->Page->showSoalPMB==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('SoalPMB',true)%>">
                                                <i class="fa fa-list-ul"></i>
                                                <span>Soal Ujian PMB</span>											
                                            </a>                                        
                                        </li>
                                    </com:TLiteral>	
                                    <com:TLiteral Visible="<%=$this->Page->Pengguna->getTipeUser()=='d'%>">
                                        <li<%=$this->Page->showDashboard==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('Home',true)%>">
                                                <i class="fa fa-dashboard"></i>
                                                <span>Dashboard</span>											
                                            </a>                                        
                                        </li>                                         
                                    </com:TLiteral>	
                                    <com:TLiteral Visible="<%=$this->Page->Pengguna->getTipeUser()=='dw'%>">
                                        <li<%=$this->Page->showDashboard==true?' class="active"':''%>>
                                            <a href="<%=$this->Page->constructUrl('Home',true)%>">
                                                <i class="fa fa-dashboard"></i>
                                                <span>Dashboard</span>											
                                            </a>                                        
                                        </li>                                         
                                    </com:TLiteral>	
								</ul>
							</div>
						</div>
					</section>
					<div id="nav-col-submenu"></div>
				</div>
				<div id="content-wrapper">
					<div class="row">
						<div class="col-lg-12">
							<div class="row">
								<div class="col-lg-12">
									<ol class="breadcrumb">
										<li><a href="<%=$this->Page->constructUrl('Home',true)%>">Home</a></li>
										<com:TContentPlaceHolder ID="modulebreadcrumb" />
									</ol>
									<h1><com:TContentPlaceHolder ID="moduleheader" /></h1>
								</div>
                                <com:TContentPlaceHolder ID="maincontent" /> 
							</div>
						</div>
					</div>					
					<footer id="footer-bar" class="row">
						<p id="footer-copyright" class="col-xs-12">                        
                            <%=$this->Application->getID()%> licensed to STISIPOL Raja Haji Tanjungpinang Powered by <a href="https://www.yacanet.com">Yacanet.com</a>
                            <com:TJavascriptLogger />
                        </p>
					</footer>
				</div>
			</div>
		</div>
	</div>	
	<com:TContentPlaceHolder ID="configtools" />
</com:TForm>
<script src="<%=$this->Page->Theme->baseUrl%>/js/jquery.js"></script>
<script type="text/javascript">
    jQuery.noConflict();
</script>
<script src="<%=$this->Page->Theme->baseUrl%>/js/bootstrap.min.js"></script>
<script src="<%=$this->Page->Theme->baseUrl%>/js/jquery.nanoscroller.min.js"></script>				
<com:TContentPlaceHolder ID="jscontent" />	
<script src="<%=$this->Page->Theme->baseUrl%>/js/scripts.js"></script>
<script src="<%=$this->Page->Theme->baseUrl%>/js/pace.min.js"></script>	
<script src="<%=$this->Page->Theme->baseUrl%>/js/portalekampus.js" type="text/javascript"></script>
<!-- this page specific inline scripts -->
<com:TContentPlaceHolder ID="jsinlinecontent" />	
</body>
</html>