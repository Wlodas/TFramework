<?php

namespace TFramework\Widget;

class InputHidden extends Widget
{
	protected $is_visible = false;
	
	public function renderLabel(){}
	
	public function render()
	{
		if(!empty($this->value)) {
			printf('<input id="%s" type="hidden" value="%s" />', $this->getId(), $this->getFieldName(), str_replace('"', '&quot;', $this->getValue()));
		} else {
			printf('<input id="%s" type="hidden" />', $this->getId(), $this->getFieldName());
		}
	}
}