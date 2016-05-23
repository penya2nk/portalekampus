<com:NModalPanel ID="modalMessage" CssClass="md-modal md-effect-1 md-show">
    <div class="md-content" style="background-color: #3a87ad;color:#fff">
        <div class="modal-header">                
            <h4 class="modal-title"><i class="fa fa-info-circle"></i> <strong>Info: </strong><com:TActiveLabel ID="lblInfo" /></h4>
        </div>
        <div class="modal-body">
            <com:TActiveLabel ID="lblMessageInfo" />
            <com:TActiveHyperLink ID="linkOutput" />        
        </div>
        <div class="modal-footer"  style="background-color: #696969">
            <div class="row">
                <div class="col-sm-10 text-left">
                    
                </div>
                <div class="col-sm-2">
                    <a OnClick="new Modal.Box('<%=$this->modalMessage->ClientID%>').hide();return false;" class="btn btn-default"><i class='icon-off'></i> Close</a>                              
                </div>
            </div>
            
        </div>     
    </div>      
</com:NModalPanel>