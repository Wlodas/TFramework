<?php

namespace TFramework\Widget;

class InputText extends Widget
{
	public function render()
	{
		if(!empty($this->value)) {
			printf('<input id="%s" type="text" name="%s" value="%s" />', $this->getId(), $this->getFieldName(), str_replace('"', '&quot;', $this->getValue()));
		} else {
			printf('<input id="%s" name="%s" type="text" />', $this->getId(), $this->getFieldName());
		}
	}
}