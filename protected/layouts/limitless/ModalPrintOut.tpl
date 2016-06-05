<com:NModalPanel ID="modalPrintOut" CssClass="md-modal md-effect-1 md-show">
    <div class="md-content" style="background-color: #3a87ad;color:#fff">
        <div class="modal-header">                
            <h4 class="modal-title"><i class="icon-printer"></i> <strong>Print Out </strong><com:TActiveLabel ID="lblPrintout" /></h4>
        </div>
        <div class="modal-body">
            <com:TActiveLabel ID="lblMessagePrintout" />
            <com:TActiveHyperLink ID="linkOutput" />        
        </div>
        <div class="modal-footer" style="background-color: #696969">
            <div class="row">
                <div class="col-md-10 text-left">
                    
                </div>
                <div class="col-md-2">
                    <br/>
                    <button OnClick="new Modal.Box('<%=$this->modalPrintOut->ClientID%>').hide();return false;" class="btn btn-primary"><i class='icon-exit'></i> Close</a>                              
                </div>
            </div>            
        </div>     
    </div>      
</com:NModalPanel>