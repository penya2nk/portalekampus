<!DOCTYPE html>
<html lang="id">
<com:THead>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<%=$this->Application->getID()%>">        
	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="<%=$this->Page->Theme->BaseUrl%>/assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
	<link href="<%=$this->Page->Theme->BaseUrl%>/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="<%=$this->Page->Theme->BaseUrl%>/assets/css/core.min.css" rel="stylesheet" type="text/css">
	<link href="<%=$this->Page->Theme->BaseUrl%>/assets/css/components.css" rel="stylesheet" type="text/css">
	<link href="<%=$this->Page->Theme->BaseUrl%>/assets/css/colors.min.css" rel="stylesheet" type="text/css">
    <link type="image/x-icon" href="resources/favicon.ico" rel="shortcut icon"/>
    <com:TContentPlaceHolder ID="csscontent" />	    
</com:THead>
<body class="has-detached-right">
<com:TForm id="mainform" Attributes.role="form">
<div class="page-container">
    <div class="page-content">
        <!-- Main content -->
        <div class="content-wrapper">
            <!-- Page header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title">
                        <h4>Portal EKampus</h4>
                    </div>
                </div>
                <div class="breadcrumb-line breadcrumb-line-component">
                    <ul class="breadcrumb">
                        <li><a href="<%=$this->Page->constructUrl('Home')%>"><i class="icon-home2 position-left"></i> Home</a></li>							
                        <li><a href="<%=$this->Page->constructUrl('Pendaftaran')%>"><i class="icon-user position-left"></i> Pendaftaran Mahasiswa Baru</a></li>
                        <com:TContentPlaceHolder ID="modulebreadcrumb" />
                    </ul>
                    <ul class="breadcrumb-elements">
                        <li><a href="<%=$this->Page->constructUrl('Login')%>"><i class=" icon-unlocked2 position-left"></i> Login</a></li>							
                    </ul>
                </div>
            </div>            
            <div class="content">
                <div class="container-detached">
                    <div class="content-detached">
                        <com:TContentPlaceHolder ID="maincontent" />
                    </div>                    
                </div>
                <div class="sidebar-detached">
                    <div class="sidebar sidebar-default sidebar-separate">
                        <div class="sidebar-content">
                            <!-- Categories -->
                            <div class="sidebar-category">
                                <div class="category-title">
                                    <span>Kategori</span>
                                    <ul class="icons-list">
                                        <li><a href="#" data-action="collapse"></a></li>
                                    </ul>
                                </div>

                                <div class="category-content no-padding">
                                    <ul class="navigation">
                                        <li>
                                            <a href="<%=$this->Page->constructUrl('DataLulusan')%>">
                                                <span class="text-muted text-size-small text-regular pull-right">
                                                    <com:TLiteral ID="literalTotalLulusan" />
                                                </span>
                                                <i class="icon-user"></i>
                                                Data Lulusan
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer text-muted">
                    <%=$this->Application->getID()%> licensed to <%=$this->Page->setup->getSettingValue('nama_pt_alias')%> Powered by <a href="https://www.yacanet.com">Yacanet.com</a>
                </div>					
			</div>
		</div>
	</div>
</com:TForm>
<script type="text/javascript" src="<%=$this->Page->Theme->baseUrl%>/assets/js/core/libraries/jquery.min.js"></script>
<script type="text/javascript">
    jQuery.noConflict();
</script>
<script type="text/javascript" src="<%=$this->Page->Theme->baseUrl%>/assets/js/core/libraries/bootstrap.min.js"></script>
<script type="text/javascript" src="<%=$this->Page->Theme->BaseUrl%>/assets/js/plugins/loaders/pace.min.js"></script>
<script type="text/javascript" src="<%=$this->Page->Theme->BaseUrl%>/assets/js/plugins/ui/nicescroll.min.js"></script>
<script type="text/javascript" src="<%=$this->Page->Theme->BaseUrl%>/assets/js/plugins/ui/drilldown.js"></script>
<script type="text/javascript" src="<%=$this->Page->Theme->BaseUrl%>/assets/js/core/app.min.js"></script>
<script type="text/javascript" src="<%=$this->Page->Theme->baseUrl%>/assets/js/core/portalekampus.js"></script>
<com:TContentPlaceHolder ID="jscontent" />
<com:TContentPlaceHolder ID="jsinlinecontent" />
<com:TJavascriptLogger ID="loggerJS" />
</body>
</html>


