<com:NModalPanel ID="modalMessageError" CssClass="md-modal md-effect-1 md-show">
    <div class="md-content" style="background-color: #DD191D;color:#fff">
        <div class="modal-header">                
            <h4 class="modal-title"><i class="fa fa-warning fa-fw fa-lg"></i> <strong>Pesan Kesalahan </strong><com:TActiveLabel ID="lblHeaderMessageError" /></h4>
        </div>
        <div class="modal-body">
            <com:TActiveLabel ID="lblContentMessageError" />        
        </div>
        <div class="modal-footer"  style="background-color: #696969">
            <div class="row">
                <div class="col-sm-10 text-left">
                    
                </div>
                <div class="col-sm-2">
                    <a OnClick="new Modal.Box('<%=$this->modalMessageError->ClientID%>').hide();return false;" class="btn btn-default"><i class='icon-off'></i> Close</a>                              
                </div>
            </div>            
        </div>     
    </div>      
</com:NModalPanel>