<?php

$db_info = array(
	'dbType' => 'mysql',
	'dbHost' => 'mariadb',
	'dbName' => 'helios', 
	'dbUser' => 'root',
	'dbPassword' => 'rootroot'
);


$smtp_settings = array (
	 'smtpuser' => 'user@gmail.com', 
	 'smtppass' => '<<password >>',
	 'smtphost' => 'smtp.gmail.com',
	 'smtpsecure' => 'tls',
	 'smtpport' => '587',
	 'from' => 'from@gmail.com'
);

$app_settings = array(
	'DbLogging' => true,			// Log database queries naar logfile
	'DbError' => true,				// Log errors naar logfile
	'Debug' => true,				// Debug informatie naar logfile, uitzetten voor productie
	'LogDir' => '/tmp/log/helios/',	// Locatie waar log bestanden geschreven worden
	'Vereniging' => "GeZC"	
);


// Wachtwoord om te installeren
if(file_exists('installer_account.php'))
	include 'installer_account.php';

if (!IsSet($GLOBALS['DBCONFIG_PHP_INCLUDED']))
{
	include('include/database.inc.php');
	$GLOBALS['DBCONFIG_PHP_INCLUDED'] = 1;	
	
	global $db;
	$db = new DB();
	try 
	{
		$db->Connect();
	}
	catch (Exception $exception) {}
}

?>