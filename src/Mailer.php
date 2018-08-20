<?php

namespace Fridde;
use PHPMailer;

class Mailer extends PHPMailer
{

	private $receiver;

	// in the form of [local_alias => actual name by PHPMailer]
	private $attribute_alias = [];
	private $smtp_settings_index = 'smtp_settings';
	private static $attributes_from_settings = ['from', 'host', 'password', 'username'];

	public function __construct (array $parameters = [])
	{
		parent::__construct();
		$this->setGlobalOptions();
		$this->setConfiguration($parameters);
		$this->initialize();
	}

	private function initialize(): void
	{
		$this->isSMTP();
		$debug = DEBUG ?? false;
		$this->SMTPDebug = $this->SMTPDebug ?? ($debug ? 4 : 0);
		$this->Debugoutput = 'html';
		$this->Port = 587;
		$this->SMTPSecure = 'tsl';
		$this->SMTPAuth = true;
		$this->CharSet = 'utf-8';
        $this->Encoding = '8bit';
		$this->SMTPOptions['ssl'] = ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true];
		$this->isHTML(true);
	}

	public function compose(string $debug_address = null): void
    {
        //$this->validateCrucialAttributes();
		$this->setFrom($this->From);

        if(!empty($debug_address)){
			$this->addAddress($debug_address);
			$this->Subject = '[' . $this->receiver . '] ' . $this->Subject;
		} else {
			$this->addAddress($this->receiver);
		}
		if(empty($this->Body)){
			throw new \Exception('The message body can not be empty.');
		}
		$this->setWordWrap();
		$this->msgHTML($this->Body);
	}

	private function validateCrucialAttributes(): void
	{
		$crucial_attributes = ['From', 'receiver', 'Host', 'Password', 'Username'];
		if(empty(DEBUG)){
            $crucial_attributes[] = 'Password';
            $crucial_attributes[] = 'Username';
        }
		$optional_attributes = ['Subject', 'Body'];

		foreach($crucial_attributes as $att_name){
			if(empty($this->$att_name)){
				throw new \Exception('The crucial attribute "' . $att_name . '" has not been set.');
			}
		}
		foreach($optional_attributes as $att_name)
		{
			if(empty($this->$att_name)){
				$this->$att_name = '';
			}
		}
	}

	private function setConfiguration(array $settings = [])
	{
		array_walk($settings, function($val, $key){
			$this->set($key, $val);
		});
	}

	public function set(string $attribute, $value = null)
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
			throw new \Exception('Tried to set an attribute of the Mailer that doesn\'t exist: ' . $attribute);
		}
		$this->$valid_attribute = $value;
	}



	public function setGlobalOptions(): void
	{
		$smtp_settings = SETTINGS[$this->smtp_settings_index] ?? [];
		$possible_keys = array_flip(self::$attributes_from_settings);
		$global_options = array_intersect_key($smtp_settings, $possible_keys);
		$this->setConfiguration($global_options);
	}


	public function sendAway(string $debug_address = null)
	{
		$this->compose($debug_address);
		return $this->send();
	}
}
