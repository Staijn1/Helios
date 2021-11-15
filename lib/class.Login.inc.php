<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require ("include/PasswordHash.php");

	class Login extends Helios
	{
		private $_userID = null;			// Wie is er ingelogd
		
		public function __construct()
		{
			parent::__construct();
			
			if (session_status() == PHP_SESSION_NONE)
			{
				session_start(); 
				if(isset($_SESSION['login']))
				{
					$this->_userID = $_SESSION['login'];
				}			
				session_write_close();
			}			
		}
		
		function getUserFromSession()
		{
			global $app_settings;
			$id = -1;

			if ($this->_userID != null)			// id van ingelode gebruiker is opgeslagen in class member
			{
				$id = $this->_userID;
			}
			elseif (isset($_SESSION['login']))	// id van ingelode gebruiker is opgeslagen in sessie
			{
				$id = $_SESSION['login'];
			}
			elseif ((array_key_exists('PHP_AUTH_USER', $_SERVER)) && (array_key_exists('PHP_AUTH_PW', $_SERVER)))
			{
				$username = $_SERVER['PHP_AUTH_USER'];
				
				$lObj = $l->GetObjectByLoginNaam($username); 	// id van ingelode gebruiker via username
				$id = $lObj['ID'];
			}
			else 	// id van ingelode gebruiker uit bearer token
			{				
				$jwt = $this->getBearerToken();	

				if ($jwt)
				{
					$decoded = (array) JWT::decode($jwt, new Key($app_settings['KeyJWT'], 'HS256'));
					$id = $decoded["ID"];	
				}

				if ($id < 0)
				{
					throw new Exception("501;Gebruiker onbekend;");
				}
			}
			return $id;
		}

		function Logout()
		{
			Debug(__FILE__, __LINE__, "Logout()");
			
			session_start(); 
			if (isset($_SESSION['login']))
				unset($_SESSION['login']);

			if (isset($_SESSION['userInfo']))
				unset($_SESSION['userInfo']);				

			if (isset($_SESSION['isInstaller']))
				unset($_SESSION['isInstaller']);

			$this->_userID = null;
			session_destroy();				
		}		
		
		function setSessionUser($id)
		{
			Debug(__FILE__, __LINE__, sprintf("setSessionUser(%s)", $id));
			$this->_userID = $id;

			if (session_status() == PHP_SESSION_NONE)
				session_start();

			$_SESSION['login']= $id;
			$_SESSION['userInfo'] = json_encode($this->getUserInfo($id));

			Debug(__FILE__, __LINE__, sprintf("setSessionUser = %s", $_SESSION['userInfo'] ));
			//session_write_close();
		}

		function getUserInfo($lidID)
		{			
			$Userinfo = array();

			// initieele waarde, weten we zeker dat array gevuld is
			$Userinfo['isBeheerderDDWV'] = false;
			$Userinfo['isBeheerder'] = false;
			$Userinfo['isInstructeur'] = false;

			$Userinfo['isCIMT'] = false;
			$Userinfo['isStarttoren'] = false;
			$Userinfo['isRooster'] = false;		
			$Userinfo['isDDWVCrew'] = false;

			$Userinfo['isClubVlieger'] = false;
			$Userinfo['isDDWV'] = false;
			$Userinfo['isAangemeld'] = false;

			$LidData = null;

			Debug(__FILE__, __LINE__, sprintf("getUserInfo: %s, isInstaller:%s", $lidID, ($this->isInstaller() == true ? "true" : "false") ));

			$a = MaakObject('AanwezigLeden');

			if ((is_numeric($lidID)) && (!$this->isInstaller()))
			{	
				$l = MaakObject('Leden');
				try
				{
					$LidData = $l->getObject($lidID);
					$LidData['WACHTWOORD'] 	= "****";
				}
				catch(Exception $exception) 
				{
					Debug(__FILE__, __LINE__, "getObject($lidID) gefaald");
				}

				$Userinfo['isBeheerderDDWV'] 	= $l->isPermissie("DDWV_BEHEERDER", $LidData['ID'], $LidData);
				$Userinfo['isBeheerder'] 		= $l->isPermissie("BEHEERDER", $LidData['ID'], $LidData);
				$Userinfo['isInstructeur'] 		= $l->isPermissie("INSTRUCTEUR", $LidData['ID'], $LidData);

				$Userinfo['isCIMT'] 			= $l->isPermissie("CIMT", $LidData['ID'], $LidData);
				$Userinfo['isRooster'] 			= $l->isPermissie("ROOSTER", $LidData['ID'], $LidData);
				$Userinfo['isStarttoren'] 		= $l->isPermissie("STARTTOREN", $LidData['ID'], $LidData);
				$Userinfo['isDDWVCrew'] 		= $l->isPermissie("DDWV_CREW", $LidData['ID'], $LidData);

				$Userinfo['isClubVlieger'] 		= $l->isClubVlieger($LidData['ID'], $LidData);
				$Userinfo['isDDWV'] 			= $l->isDDWV($LidData['ID'], $LidData);
				$Userinfo['isAangemeld'] 		= $a->IsAangemeldVandaag($UserID);				
			}
			return array ("LidData" => $LidData, "Userinfo" => $Userinfo);
		}

		function heeftToegang($token = null)
		{
			global $NoPasswordIP;
			Debug(__FILE__, __LINE__, sprintf("heeftToegang(%s)", $token));

			// Indien username en wachtwword gezet zijn, via basic authenticatie. Gaan we opnieuw authoriseren
			if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
			{ 				
				$this->verkrijgToegang ($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $token);
				return;
			}

			$UserID = $this->getUserFromSession();

			// Check of sessie data valide is, zo nee dat goed zetten
			if (isset($_SESSION['userInfo']) === false) 
			{
				$this->setSessionUser($UserID);
			}
			else 
			{
				$lidData = $this->lidData();
				if ($lidData->ID != $UserID)
					$this->setSessionUser($UserID);
			}

			if(isset($UserID))
			{
				Debug(__FILE__, __LINE__, sprintf("heeftToegang: UserID=%s ", $UserID));
				return;
			}
			$this->toegangGeweigerd();		
		}

		function verkrijgToegang($username=null, $password=null, $token=null)
		{		
			global $app_settings;
			global $installer_account;
			
			Debug(__FILE__, __LINE__, sprintf("verkrijgToegang(%s, %s, %s)", $username, "??", $token)); 
			
			// Als username & wachtwoord niet zijn meegegeven, dan ophalen uit de aanvraag
			if (($username == null) || ($password == null))
			{		
				if ((array_key_exists('PHP_AUTH_USER', $_SERVER)) && (array_key_exists('PHP_AUTH_PW', $_SERVER)))
				{
					$username = $_SERVER['PHP_AUTH_USER'];
					$password = $_SERVER['PHP_AUTH_PW'];
					
					Debug(__FILE__, __LINE__, sprintf("username = %s", $username));
				}
				else
				{
					Debug(__FILE__, __LINE__, sprintf("Toegang geweigerd, geen username bekend", $username));
					$this->toegangGeweigerd();
				}
			}

			// De toegang voor de installer
			if (isset($installer_account))
			{
				$key = sha1(strtolower ($username) . $password);
				if (($username == $installer_account['username']) && ($key == $installer_account['password']))
				{	
					Debug(__FILE__, __LINE__, sprintf("helios installer account = true", $username));

					$this->setSessionUser($installer_account['id']);
					$_SESSION['isInstaller']= true;
					return;
				}	
			}
			Debug(__FILE__, __LINE__, "helios account = false");
			unset($_SESSION['isInstaller']); 	// gebruiker is zeker geen installer				
				
			// Kijken of we toegang kunnen geven
			$l = MaakObject('Leden');

			try 
			{ 
				$lObj = $l->GetObjectByLoginNaam($username); 
				Debug(__FILE__, __LINE__, sprintf("Login(%s) = %s, LIDTYPE_ID=%s", $username, $lObj["NAAM"], $lObj['LIDTYPE_ID']));
			}
			catch(Exception $exception) 
			{
				Debug(__FILE__, __LINE__, "Login: " .$exception);
				$this->toegangGeweigerd();	
			}

				
			$key = sha1(strtolower ($username) . $password);
			Debug(__FILE__, __LINE__, sprintf("Login(%s)[%s] = %s, %s, %s, %s,", 	$username, 
																				$lObj["AUTH"], 
																				$lObj["NAAM"], 
																				$lObj['WACHTWOORD'], 
																				$key, 
																				$lObj['SECRET']));
			
			// check of we 2factor kunnen overslaan, dit kan als er een cookie is
			// en de cookie hetzelfde ID heeft als de gebruiker die nu wil inloggen 
			$skip2Factor = false;
			if (isset($_COOKIE['2FACTOR']))
			{
				if ($_COOKIE['2FACTOR'] == base64_encode($lObj['ID']))	// Cookie bevat ID ten tijde van de SMS
					$skip2Factor = true;
			}


			if (($lObj['AUTH'] == "1") && (empty($token)) && $skip2Factor == false) 
			{
				Debug(__FILE__, __LINE__, sprintf("URI: %s)", $_SERVER['REQUEST_URI']));	

				// als we SMS gaan versturen, kunnen we verder
				$uri = explode('/', $_SERVER['REQUEST_URI']);
				if (count($uri) > 1)
				{
					if (strtoupper($uri[2]) == "SENDSMS") {
						return;
					}
				}

				throw new Exception("406;Token moet ingevoerd worden;");
			}
																										
			if ($lObj['WACHTWOORD'] == $key)	
			{		
				Debug(__FILE__, __LINE__, sprintf("Toegang toegestaan (%s)", $username));	

				if (($lObj['AUTH'] == "1") && ($skip2Factor == false))			// 2 factor authenticatie
				{
					$ga = new PHPGangsta_GoogleAuthenticator();
					$checkResult = $ga->verifyCode($lObj['SECRET'], $token, 2);    // 2 = 2*30sec clock tolerance

					if ($checkResult) 
					{
						Debug(__FILE__, __LINE__, sprintf("2 Factor succesvol"));	

						// We willen SMS kosten besparen, dus niet ieder inlog poging om SMS vragen
						// De startstoren en beheerder max 1 SMS perdag, alle andere gebruikers max 1x per week

						$verlopen = time()+ 60 * 60 * 24 * 7;	// na week nieuwe SMS nodig
						if (($lObj['STARTTOREN'] ==  1) || ($lObj['BEHEERDER'] ==  1)) 
							$verlopen = $timestamp = strtotime('today midnight') +  60 * 60 * 24;	// iedere dag nieuwe SMS nodig

						session_set_cookie_params(["SameSite" => "None"]); //none, lax, strict
						setcookie("2FACTOR", base64_encode($lObj['ID']), [
							'expires' => $verlopen,
							'path' => '/',
							'secure' => true,
							'samesite' => 'None',
						]);  	// stoppen ID in cookie

						$this->setSessionUser($lObj['ID']);	
						return $this->JWT($lObj);
					}
					else
					{
						Debug(__FILE__, __LINE__, sprintf("2 Factor gefaalt"));	
					}
				}
				else
				{
					$this->setSessionUser($lObj['ID']);	
					return $this->JWT($lObj);
				}
			}
			else if ($lObj['LIDTYPE_ID'] == "625")			// 625 = DDWV
			{
				$phpass = new PasswordHash(10, true);
				$ok= $phpass->CheckPassword($password, $lObj['WACHTWOORD']);
				
				if ($ok == true) 
				{
					Debug(__FILE__, __LINE__, sprintf("Toegang toegestaan DDWV (%s)", $username));	
					$this->setSessionUser($lObj['ID']);	
					return $this->JWT($lObj);							
				}
			}
			
			// Heeft geen toegang, dus einde
			Debug(__FILE__, __LINE__, sprintf("Toegang geweigerd (%s)", $username));
			$this->toegangGeweigerd();				
		}		
		
		// Geef data over gebruiker
		function lidData()
		{
			// als er session niet gezet is, kunnen we niets
			if (isset($_SESSION['userInfo']) === false)
				return null;

			// ophalen van de gebruikers info als en decodeer JSON data
			$ui = json_decode($_SESSION['userInfo']);

			// als er userInfo niet gezet is, gaan we opnieuw voor de veilige oplossing
			if (property_exists($ui, 'LidData') === false)
				return null;
				
			return $ui->LidData;
		}

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function sessiePermissie($key)
		{	
			// als er session niet gezet is, gaan we voor de veilige oplossing
			if (isset($_SESSION['userInfo']) === false)
			{
				Debug(__FILE__, __LINE__, sprintf("sessiePermissie userInfo BESTAAT NIET")); 
				return false;
			}

			// ophalen van de gebruikers info als en decodeer JSON data
			$ui = json_decode($_SESSION['userInfo']);

			// als er userInfo niet gezet is, gaan we opnieuw voor de veilige oplossing
			if (property_exists($ui, 'Userinfo') === false)
			{
				Debug(__FILE__, __LINE__, sprintf("sessiePermissie(%s) UserInfo BESTAAT NIET", $key)); 
				return false;
			}
			
			// De key die we zoeken bestaat niet, dus helaas	
			if (property_exists($ui->Userinfo, $key) === false)
			{
				Debug(__FILE__, __LINE__, sprintf("sessiePermissie(%s) BESTAAT NIET IN UserInfo", $key)); 
				return false;	
			}
	
			if (($ui->Userinfo->$key) || ($ui->Userinfo->$key == 1))
				return true;

			return false;
		}

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isInstructeur()
		{	
			$key = 'isInstructeur';
			return $this->sessiePermissie($key);
		}

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isCIMT()
		{	
			$key = 'isCIMT';
			return $this->sessiePermissie($key);
		}

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isRooster()
		{	
			$key = 'isRooster';
			return $this->sessiePermissie($key);
		}	
		
		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isStarttoren()
		{	
			$key = 'isStarttoren';
			return $this->sessiePermissie($key);
		}			

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isBeheerder()
		{	
			$key = 'isBeheerder';
			return $this->sessiePermissie($key);

		}	
		
		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isBeheerderDDWV()
		{			
			$key = 'isBeheerderDDWV';
			return $this->sessiePermissie($key);
		}			

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isDDWVCrew()
		{			
			$key = 'isDDWVCrew';
			return $this->sessiePermissie($key);
		}	

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isDDWV()
		{			
			$key = 'isDDWV';
			return $this->sessiePermissie($key);
		}

		// Deze data komt uit de sessie, bij het inloggen is de sessie data gezet
		function isClubVlieger()
		{			
			$key = 'isClubVlieger';
			return $this->sessiePermissie($key);
		}

		function isInstaller()
		{				
			if (array_key_exists('isInstaller', $_SESSION) === false)
				return false;
	
			return $_SESSION['isInstaller'];
		}		
			
		function toegangGeweigerd()
		{
			throw new Exception("401;Toegang geweigerd;");
		}

		// Maak JSON Web Token (JWT)
		function JWT($lidData)
		{
			global $app_settings;

			$payload = array(
				"iss" => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]",
				"aud" => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]",
				"iat" => time(),
				"exp" => time() + 15 * 60,		// 15 min geldig
				"ID" => $lidData['ID'] 
			);
			
			return JWT::encode($payload, $app_settings['KeyJWT'], 'HS256');
		}

		function getAuthorizationHeader(){
			$headers = null;
			if (isset($_SERVER['Authorization'])) {
				$headers = trim($_SERVER["Authorization"]);
			}
			else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
				$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
			} elseif (function_exists('apache_request_headers')) {
				$requestHeaders = apache_request_headers();
				// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
				$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
				//print_r($requestHeaders);
				if (isset($requestHeaders['Authorization'])) {
					$headers = trim($requestHeaders['Authorization']);
				}
			}
			return $headers;
		}

		/**
		 * get access token from header
		 * */
		function getBearerToken() {
			$headers = $this->getAuthorizationHeader();
			// HEADER: Get the access token from the header
			if (!empty($headers)) {
				if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
					return $matches[1];
				}
			}
			return null;
		}		

		function sendSMS() 
		{
			Debug(__FILE__, __LINE__, "sendSMS()");

			global $app_settings;

			if (!array_key_exists('PHP_AUTH_USER', $_SERVER)) 
			{
				throw new Exception("406;Geen login naam in aanroep;");
			}

			$l = MaakObject('Leden');
			$lObj = $l->GetObjectByLoginNaam($_SERVER['PHP_AUTH_USER']); 

			Debug(__FILE__, __LINE__, sprintf("sendSMS username:%s mobiel:%s", $_SERVER['PHP_AUTH_USER'], $lObj['MOBIEL'])); 
			
			$ga = new PHPGangsta_GoogleAuthenticator();

			$MessageBird = new \MessageBird\Client($app_settings['ApiKeySMS']);
			$Message = new \MessageBird\Objects\Message();
			$Message->originator = $app_settings['Vereniging'];
			$Message->recipients = array($lObj['MOBIEL']);
			$Message->body = 'Uw code: ' . $ga->getCode($lObj['SECRET']);

			$reponse = $MessageBird->messages->create($Message);
			Debug(__FILE__, __LINE__, sprintf("sendSMS response: %s", print_r($reponse, true))); 
		}

		function GetObjects($params)
		{
			// Doe niets. 
			// Deze functie is alleen maar nodig omdat de abstract class helios (= parent) afdwingt dat we deze functie moeten implementeren
		}
	}
