<?php

namespace Fridde;


class Mailer extends \PHPMailer\PHPMailer\PHPMailer
{
	private $sender;
	private $receiver;

	// in the form of [local_alias => actual name by PHPMailer]
	private $attribute_alias = ["to" => "receiver", "from" => "sender"];
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
		$this->isHTML = true;
	}

	public function compose(){
		$this->validateCrucialAttributes();
		$this->setFrom($this->sender);
		$this->addAddress($this->receiver);
		if(empty($this->Body)){
			throw new \Exception("The message body can not be empty.");
		}
		$this->msgHTML($this->Body);
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

	private function setConfiguration($settings = [])
	{
		array_walk($settings, function($val, $key){
			$this->set($key, $val);
		});
	}

	public function set($attribute, $value = null)
	{
		$names_to_check = [$attribute, ucfirst($attribute)];
		$names_to_check[] = $this->attribute_alias[$attribute] ?? null;

		$valid_attribute = null;
		foreach($names_to_check as $name){
			if(property_exists($this, $name)){
				$valid_attribute = $name;
				break;
			}
		}
		if(empty($valid_attribute)){
			throw new Exception("Tried to set an attribute of the Mailer that doesn't exist.");
		}
		$this->$valid_attribute = $value;
	}



	public function setGlobalOptions()
	{
		$global_options = $GLOBALS["SETTINGS"][$this->smtp_settings_index] ?? [];
		$this->setConfiguration($global_options);
	}


	public function send()
	{
		$this->compose();
		bdump($this);
		$success = $this->send();
		if (!$success) {
			echo "Mailer Error: " . $this->ErrorInfo;
		} else {
			echo "Message sent!";
		}
	}
}
