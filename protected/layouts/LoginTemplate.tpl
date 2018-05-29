<!DOCTYPE html>
<html lang="id">
<com:THead>    
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">    
 	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;subset=latin" rel="stylesheet">    
	<link href="<%=$this->Page->Theme->baseUrl%>/css/bootstrap.min.css" rel="stylesheet">
	<link href="<%=$this->Page->Theme->baseUrl%>/css/nifty.min.css" rel="stylesheet">
	<link href="<%=$this->Page->Theme->baseUrl%>/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="<%=$this->Page->Theme->baseUrl%>/plugins/pace/pace.min.css" rel="stylesheet">
    <link type="image/x-icon" href="resources/favicon.ico" rel="shortcut icon"/>
    <script src="<%=$this->Page->Theme->baseUrl%>/plugins/pace/pace.min.js"></script>
    <style type="text/css">
		.my-bg{
			background-image : url("resources/bg.jpg");
		}
	</style>    
</com:THead>
<body>        
<div id="container" class="cls-container">
    <div id="bg-overlay" class="bg-img my-bg"></div>
    <div class="cls-header cls-header-lg">
        <div class="cls-brand">
            <a class="box-inline" href="<%=$this->Page->constructUrl('Home')%>">                
                <span class="brand-title">Portal E-Kampus<span class="text-thin"> <%=$this->Page->setup->getSettingValue('nama_pt_alias')%></span></span>
            </a>
        </div>
    </div>
    <com:TPanel CssClass="cls-content" Visible="<%=$this->User->isGuest==true%>">
        <div class="cls-content-sm panel">
            <div class="panel-body">
                <p class="pad-btm"><com:TContentPlaceHolder ID="messagewelcome" /></p>
                <com:TForm Attributes.role="form">   
                    <com:TContentPlaceHolder ID="maincontent" />
                </com:TForm>										
            </div>
            <com:TPanel CssClass="pad-all" Visible="<%=!$this->Page->getLogic('Setup')->isSecure()%>">
                <a href="https://<%=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']%>" class="btn-link mar-lft" style="color:green">Secure Connection</a>
            </com:TPanel>
        </div>
    </com:TPanel>    
    <com:TPanel CssClass="cls-content" Visible="<%=$this->User->isGuest==false%>">
        <div class="cls-content-sm panel">
            <div class="panel-body">
                <p class="pad-btm">Anda sudah melakukan login, silahkan klik <a href="<%=$this->Page->constructUrl('Home',true)%>" style="color:blue;">disini</a> untuk kembali.</p>               								
            </div>
        </div>
    </com:TPanel>
</div>
<script src="<%=$this->Page->Theme->baseUrl%>/js/jquery-2.1.1.min.js"></script>
<script type="text/javascript">
    jQuery.noConflict();
</script>
<script src="<%=$this->Page->Theme->baseUrl%>/js/bootstrap.min.js"></script>
<script src="<%=$this->Page->Theme->baseUrl%>/plugins/fast-click/fastclick.min.js"></script>
<script src="<%=$this->Page->Theme->baseUrl%>/js/nifty.min.js"></script>
</body>
</html>
