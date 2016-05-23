<com:TPanel CssClass="row" ID="panelInfoDosen">
    <div class="col-lg-12">
        <div class="main-box clearfix">
            <header class="main-box-header clearfix">
                <h2 class="pull-left"><i class="fa fa-user"></i> Informasi Dosen</h2> 
                <div class="filter-block pull-right">                                           
                    
                </div>
            </header>
            <div class="main-box-body clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>NIP YAYASAN: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->DMaster->DataDosen['nipy']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>NIDN: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->DMaster->DataDosen['nidn']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>NAMA DOSEN: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->DMaster->DataDosen['nama_dosen']%></p>
                                </div>                            
                            </div>                            
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>JABATAN FUNGSIONAL: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->DMaster->DataDosen['nama_jabatan']%></p>
                                </div>                            
                            </div>  
                        </div>
                    </div>                    
                    <div class="col-sm-7">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><strong>ALAMAT: </strong></label>
                                <div class="col-sm-9">
                                    <p class="form-control-static"><%= $this->DMaster->DataDosen['alamat_dosen']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><strong>NO. TELP: </strong></label>
                                <div class="col-sm-9">
                                    <p class="form-control-static"><%= $this->DMaster->DataDosen['nama_semester']%></p>
                                </div>                            
                            </div>  
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><strong>EMAIL: </strong></label>
                                <div class="col-sm-9">
                                    <p class="form-control-static"><%= $this->DMaster->DataDosen['email']%></p>
                                </div>                            
                            </div>                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><strong>WEBSITE: </strong></label>
                                <div class="col-sm-9">
                                    <p class="form-control-static"><%= $this->DMaster->DataDosen['website']%></p>
                                </div>                            
                            </div>                            
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>
</com:TPanel>