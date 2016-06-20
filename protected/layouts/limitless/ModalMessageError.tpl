<com:NModalPanel ID="modalMessageError" CssClass="md-modal md-effect-1 md-show">
    <div class="md-content" style="background-color: #DD191D;color:#fff">
        <div class="modal-header">                
            <h4 class="modal-title"><i class="icon-warning"></i> <strong>Pesan Kesalahan </strong><com:TActiveLabel ID="lblHeaderMessageError" /></h4>
        </div>
        <div class="modal-body">
            <com:TActiveLabel ID="lblContentMessageError" />        
        </div>
        <div class="modal-footer"  style="background-color: #696969">
            <div class="row">
                <div class="col-sm-10 text-left">
                    
                </div>
                <div class="col-sm-2">
                    <br/>                                                 
                    <button OnClick="new Modal.Box('<%=$this->modalMessageError->ClientID%>').hide();return false;" class="btn btn-default"><i class='icon-exit'></i> Close</button>                             
                </div>
            </div>            
        </div>     
    </div>      
</com:NModalPanel>