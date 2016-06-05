<?php
class TActiveCustomPager extends TActivePager
{
	protected function getTagName()
	{
		return 'div';
	}    
	protected function createPagerButton($buttonType,$enabled,$text,$commandName,$commandParameter)
	{
		if($buttonType===TPagerButtonType::LinkButton)
		{
			if($enabled)
				$button=new TActiveLinkButton;
			else
			{
				$button=new TLabel;
				$button->setText($text);                
                $button->setCssClass($this->getButtonCssClass().' active');
				return $button;
			}
		}	
		$button->setText($text);        
		$button->setCommandName($commandName);
		$button->setCommandParameter($commandParameter);
		$button->setCausesValidation(false);
        $button->setCssClass($this->getButtonCssClass());
		
		$button->attachEventHandler('OnCallback', array($this, 'handleCallback'));		
		$button->getAdapter()->getBaseActiveControl()->setClientSide(
			$this->getClientSide()
		);		
		return $button;
	}	
}

