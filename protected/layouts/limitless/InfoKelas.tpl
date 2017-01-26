<div class="row">
    <div class="col-md-12">
        <div class="panel panel-flat border-top-info border-bottom-info">
            <div class="panel-heading">
                <h5 class="panel-title"><i class="icon-three-bars"></i> Informasi Kelas</h5>
            </div>            
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>KODE: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['kmatkul']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>NAMA: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['nmatkul']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>SKS: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['sks']%></p>
                                </div>                            
                            </div>       
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>SEMESTER: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['semester']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>DOSEN PENGAJAR: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['nama_dosen']%> [<%= $this->Demik->InfoKelas['nidn']%>]</p>
                                </div>                            
                            </div> 
                        </div>
                    </div>                    
                    <div class="col-sm-6">
                        <div class="form-horizontal">                            
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>KELAS: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['namakelas']%> [<%= $this->Demik->InfoKelas['nidn']%>]</p>
                                </div>                            
                            </div> 
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>HARI: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['hari']%></p>
                                </div>                            
                            </div> 
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>JAM: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['jam_masuk'].'-'.$this->Demik->InfoKelas['jam_keluar']%></p>
                                </div>                            
                            </div> 
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>JUMLAH PESERTA: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['jumlah_peserta']%></p>
                                </div>                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
