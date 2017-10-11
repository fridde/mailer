<?php

namespace Fridde;
use PHPMailer;

class Mailer extends PHPMailer
{

	private $receiver;

	// in the form of [local_alias => actual name by PHPMailer]
	private $attribute_alias = [];
	private $smtp_settings_index = "smtp_settings";
	private $attributes_from_settings = ["from", "host", "password", "username"];

	function __construct ($parameters = [])
	{
		parent::__construct();
		$this->setGlobalOptions();
		$this->setConfiguration($parameters);
		$this->initialize();
	}

	private function initialize($debug = false)
	{
		$this->isSMTP();
		$debug = DEBUG ?? false;
		$this->SMTPDebug = $debug ? 4 : 0;
		$this->Debugoutput = 'html';
		$this->Port = 587;
		$this->SMTPSecure = 'tsl';
		$this->SMTPAuth = true;
		$this->CharSet = 'UTF-8';
		$this->SMTPOptions['ssl'] = ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true];
		$this->isHTML(true);
	}

	public function compose($debug_address = null){
		$this->validateCrucialAttributes();
		$this->setFrom($this->From);

        if(!empty($debug_address)){
			$this->addAddress($debug_address);
			$this->Subject = '[' . $this->receiver . '] ' . $this->Subject;
		} else {
			$this->addAddress($this->receiver);
		}
		if(empty($this->Body)){
			throw new \Exception("The message body can not be empty.");
		}
		$this->msgHTML($this->Body);
	}

	private function validateCrucialAttributes()
	{
		$crucial_attributes = ["From", "receiver", "Host", "Password", "Username"];
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
			throw new \Exception("Tried to set an attribute of the Mailer that doesn't exist: " . $attribute);
		}
		$this->$valid_attribute = $value;
	}



	public function setGlobalOptions()
	{
		$smtp_settings = SETTINGS[$this->smtp_settings_index] ?? [];
		$possible_keys = array_flip($this->attributes_from_settings);
		$global_options = array_intersect_key($smtp_settings, $possible_keys);
		$this->setConfiguration($global_options);
	}


	public function sendAway($debug_address = null)
	{
		$this->compose($debug_address);
		return $this->send();
	}
}
