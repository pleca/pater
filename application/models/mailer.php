<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(SYS_DIR . '/libraries/phpmailer/class.phpmailer.php');

class Mailer extends PHPMailer {

	function __construct() {
		$this->CharSet = 'utf-8';
		$this->SetLanguage('en', SYS_DIR . '/libraries/phpmailer/language/');
		$this->isMail(true); // wysylamy przez funkcje mail();
	}

	/* funkcja wysyła e-mail w formacie html */

	function SendHTML($rcptTo = '', $replyTo = '') {
		$this->isHTML(true);
		$this->AltBody = '';

		return $this->MySend($rcptTo, $replyTo);
	}

// end SendHTML()

	/* funkcja wysyla e-mail jako zwykly tekst */

	function SendPlain($rcptTo = '', $replyTo = '') {
		$this->isHTML(false);
		unset($this->AltBody);

		return $this->MySend($rcptTo, $replyTo);
	}

// end SendPlain()

	/* funkcja wysyla maila za pomoca klasy PHP-Mailer */

	function MySend($rcptTo = '', $replyTo = '') {
		if (!empty($rcptTo))
			$this->AddAddress($rcptTo);
		if (!empty($replyTo)) {
			$this->From = $replyTo;
			$this->FromName = COMPANY_NAME;
			$this->AddReplyTo($replyTo);
		} else {
			$this->From = EMAIL_OFFICE;
			$this->FromName = COMPANY_NAME;
		}

		if ($this->Send()) {
			return true;
		} else {
			Cms::getFlashBag()->add('error', $this->getError());
			return false;
		}
	}

// end MySend()	

	/* 	funkcja ustawia nadawce listu 
	  function setFrom($from = '', $fromName = '')
	  {
	  if(!empty($from))
	  {
	  $this -> From = $from;
	  if(!empty($fromName))
	  {
	  $this -> FromName = $fromName;
	  }else
	  {
	  $this -> FromName = $this -> From;
	  }
	  }
	  return true;
	  }// end setFrom */

	/* funkcja ustawia temat listu */

	function setSubject(&$subject) {
		$this->Subject = & $subject;
		return true;
	}

// end setSubject

	/* funkcja ustawia tresc listu (HTML lub plain/text) */

	function setBody(&$body) {
		$this->Body = & $body;
		return true;
	}

// end setBody

	function getError() {
		return $this->ErrorInfo;
	}

// end getError
}
