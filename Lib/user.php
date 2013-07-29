<?php

/**
 * 
 * @author Michael
 *
 */

class user {
	protected $db;
	protected $salt = "jim2"; // works as a hash key
	
	public function __construct($db){
		$this->db = $db;
	}//end construct
	
	public function login($username, $password){
		
		$usrname = (string) $username->ret_val();
		$pssword = (string) $password->ret_val();
		
		$checkLogin["sql"] = "
			SELECT CardNo, Usr, UsrInitials, UsrActive, UsrPassword, Name
			FROM JimCardFile
			WHERE UsrInitials = ?
			AND UsrPassword = ? ";
		
		$checkLogin["params"] = array( &$usrname, &$pssword );

		$return = $this->db->select( $checkLogin["sql"], $checkLogin["params"] );
		
		return $return; 
		
	}//end login
	
	public function setUserData($rs_login){
		/*
		 * Set Session Details
		*/
		$_SESSION["user"]["CardNo"] = $rs_login[0]["CardNo"];
		$_SESSION["user"]["UsrInitials"] = $rs_login[0]["UsrInitials"];
		$_SESSION["user"]["Name"] = $rs_login[0]["Name"];
		$_SESSION["user"]["auth"] = md5( $rs_login[0]["UsrInitials"] . $this->salt );
		
		date_default_timezone_set('Australia/Melbourne');
		$_SESSION["global"]["datetime"] = (string) date( 'Y-m-d H:i:s', time() );
		
		/*
		 * Set Login Cookie
		*/
		$expire=time()+60*60*24*30;
		setcookie("token", $_SESSION["user"]["auth"], $expire);
		
	}//end setUserData
	
	public function addLoginXML($rs_login){
		
		$xml_path = dirname(__DIR__).'/xml/login.xml';
		$login = file_get_contents($xml_path);
		
		$xml = new SimpleXMLElement($login);
		
		$new = $xml->addChild('details');
		$new->addChild('UsrInitials', $rs_login[0]["UsrInitials"]);
		$new->addChild('Name', $rs_login[0]["Name"]);
		$new->addChild('CardNo', $rs_login[0]["CardNo"]);
		$new->addChild('token', $_SESSION["user"]["auth"]);
		
		$xml->asXml($xml_path);
		
	}//end addLoginXML
	
	public function checkLoginXML($token){
		
		$xml_path = dirname(__DIR__).'/xml/login.xml';
		$login = file_get_contents($xml_path);
		
		$xml = new SimpleXMLElement($login);
		
		$return = array();
		foreach ($xml as $key){
			if ( (string)$key->token == (string)$token ) {
				$return[0] = json_decode(json_encode((array)$key), TRUE);
				return $return;
			}
		}
		
		return $return;
		
	}//end checkLoginXML
	
}//end user