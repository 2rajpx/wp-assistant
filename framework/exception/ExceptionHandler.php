<?php

namespace assistant\exception;

class ExceptionHandler extends \Exception {
	
	public function errorMessage() {
		//error message
		$errorMsg = '<pre>Error on line '.$this->getLine().' in '.$this->getFile()
		.': <b>'.$this->getMessage().'</b> is not a valid E-Mail address';
		return $errorMsg;
	}

}