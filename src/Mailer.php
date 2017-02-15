<?php

namespace Fridde;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer extends PHPMailer
{
	private $sender;
	private $receiver;
	private $settings_attribute_map = ["to" => "receiver", "from" => "sender"];
	private $smtp_settings_index = "smtp_settings";
	private $html_body;

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

		if(!empty($this->Body)){
			$this->msgHTML($this->Body);
		} else {
			throw new \Exception("The message body can not be empty.");
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

}
