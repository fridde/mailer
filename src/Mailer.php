<?php

namespace Fridde;

use Fridde\HTML as H;

class Mailer extends \PHPMailer\PHPMailer\PHPMailer
{
	private $sender;
	private $receiver;
	private $settings_attribute_map = ["to" => "receiver", "from" => "sender"];
	private $smtp_settings_index = "smtp_settings";

	function __construct ($parameters = [])
	{
		parent::__construct();
		$this->setGlobalOptions();
		$this->setConfiguration($parameters);
		$this->initialize();
	}

	private function initialize()
	{
		$this->isSMTP();
		$this->SMTPDebug = $GLOBALS["debug"] == true ? 4 : 0;
		$this->Debugoutput = 'html';
		$this->Port = 587;
		$this->SMTPSecure = 'tsl';
		$this->SMTPAuth = true;
		$this->CharSet = 'UTF-8';
		$this->SMTPOptions['ssl'] = ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true];
	}
	/**
	* [Summary].
	*
	* [Description]
	*
	* @param [Type] $[Name] [Argument description]
	*
	* @return [type] [name] [description]
	*/
	public function compose(){
		$this->validateCrucialAttributes();
		$this->addAddress($this->receiver);

		if(is_object($this->Body)){
			$this->msgHTML($this->Body->saveHtml());
		} else {
			$message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';
			$message .= $this->Body . '</body></html>';
			$this->msgHTML($message);
		}
		$this->setFrom($this->sender);
	}

	private function validateCrucialAttributes()
	{
		$crucial_attributes = ["sender", "receiver", "Host", "Password", "Username"];
		$optional_attributes = ["Subject", "Body"];

		foreach($crucial_attributes as $att_name){
			if(empty($this->$att_name)){
				throw new \Exception("The crucial attribute '$att_name' has not been set.");
			}
		}
		foreach($optional_attributes as $att_name)
		{
			if(empty($this->$att_name)){
				$this->$att_name = "";
			}
		}
	}

	/**
	* [Summary].
	*
	* [Description]
	*
	* @param [Type] $[Name] [Argument description]
	*
	* @return [type] [name] [description]
	*/
	public function setConfiguration($settings = [])
	{
		foreach($settings as $key => $value){
			$attribute_name = $this->settings_attribute_map[$key] ?? $key;
			if(!property_exists($this, $attribute_name)){
				$attribute_name = ucfirst($attribute_name);
			}
			$this->$attribute_name = $value;
		}
	}


	/**
	 * [setGlobalOptions description]
	 */
	public function setGlobalOptions()
	{
		$global_options = $GLOBALS["SETTINGS"][$this->smtp_settings_index] ?? [];
		$this->setConfiguration($global_options);
	}

	/**
	* [Summary].
	*
	* [Description]
	*
	* @param [Type] $[Name] [Argument description]
	*
	* @return [type] [name] [description]
	*/
	public function send()
	{
		$this->compose();
		dump($this);
		$success = parent::send();
		if (!$success) {
			echo "Mailer Error: " . $this->ErrorInfo;
		} else {
			echo "Message sent!";
		}
	}

	/**
	 * [addToBody description]
	 */
	public function addToBody()
	{
		$H = $this->Body ?? new H();
		$H->add(func_num_args());
		$this->Body = $H;
	}

	public function addHeader($header = "", $level = 1)
	{
		$this->addToBody("h" . $level, $header);
	}

	public function addRow($row = "")
	{
		$H->addToBody("p", $row);
	}
}
