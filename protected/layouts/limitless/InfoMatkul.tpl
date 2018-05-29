<div class="row">
    <div class="col-md-12">
        <div class="panel panel-flat border-top-info border-bottom-info">
            <div class="panel-heading">
                <h5 class="panel-title"><i class="icon-three-bars"></i> Informasi Matakuliah</h5>
            </div>            
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-md-4 control-label"><strong>KODE: </strong></label>
                                <div class="col-md-8">
                                    <p class="form-control-static"><%= $this->getInfoMatkul('kmatkul')%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label"><strong>NAMA: </strong></label>
                                <div class="col-md-8">
                                    <p class="form-control-static"><%= $this->getInfoMatkul('nmatkul')%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label"><strong>SKS: </strong></label>
                                <div class="col-md-8">
                                    <p class="form-control-static"><%= $this->getInfoMatkul('sks')%></p>
                                </div>                            
                            </div>                            
                        </div>
                    </div>                    
                    <div class="col-md-6">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-md-4 control-label"><strong>SEMESTER: </strong></label>
                                <div class="col-md-8">
                                    <p class="form-control-static"><%= $this->getInfoMatkul('semester')%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label"><strong>DOSEN PENGAMPU UTAMA: </strong></label>
                                <div class="col-md-8">
                                    <p class="form-control-static"><%= $this->getInfoMatkul('nama_dosen')%> [<%= $this->getInfoMatkul('nidn')%>]</p>
                                </div>                            
                            </div>                            
                            <div class="form-group">
                                <label class="col-md-4 control-label"><strong>JUMLAH PESERTA: </strong></label>
                                <div class="col-md-8">
                                    <p class="form-control-static"><%= $this->getInfoMatkul('jumlah_peserta')%></p>
                                </div>                            
                            </div>
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>
</div>