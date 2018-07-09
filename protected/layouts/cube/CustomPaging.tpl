<div class="box-footer clearfix">    
    <div class="pull-left">
        <com:TActiveLabel ID="paginationInfo"/>        
    </div>
    <div class="pull-right">        
        <com:TActiveCustomPager ID="pager" OnCallBack="Page.renderCallback" ControlToPaginate="RepeaterS" Mode="Numeric" OnPageIndexChanged="Page.Page_Changed" PrevPageText="&laquo; Previous" NextPageText="Next &raquo;" PageButtonCount="10" FirstPageText="First" LastPageText="Last" CssClass="custompaging" ButtonCssClass="page">	
            <prop:ClientSide.OnPreDispatch>
                $('loading').show();                                                                                                                 
            </prop:ClientSide.OnPreDispatch>                    
            <prop:ClientSide.onComplete>                                            
                $('loading').hide();
            </prop:ClientSide.OnComplete>
        </com:TActiveCustomPager>            
    </div>    
</div>


