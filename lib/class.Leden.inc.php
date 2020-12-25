<?php
	class Leden extends StartAdmin
	{
		function __construct() 
		{
			parent::__construct();
			$this->dbTable = "ref_leden";
		}
		
		/*
		Aanmaken van de database tabel. Indien FILLDATA == true, dan worden er ook voorbeeld records toegevoegd 
		*/		
		function CreateTable($FillData = null)
		{
			$l = MaakObject('Login');
			if ($l->isInstaller() == false)
				throw new Exception("401;Geen installer;");
				
			if (parent::bestaatTabel())	
				throw new Exception("405;Tabel bestaat al;");
				
			$query = sprintf ("
				CREATE TABLE `%s` (
                    `ID` mediumint  UNSIGNED NOT NULL AUTO_INCREMENT,
                    `NAAM` varchar(255) NOT NULL,
					`VOORNAAM` varchar(15) DEFAULT NULL,
					`TUSSENVOEGSEL` varchar(8) DEFAULT NULL,
					`ACHTERNAAM` varchar(30) DEFAULT NULL,
					`ADRES` varchar(50) DEFAULT NULL,
					`POSTCODE` varchar(10) DEFAULT NULL,
					`WOONPLAATS` varchar(50) DEFAULT NULL,
                    `TELEFOON` varchar(255) DEFAULT NULL,
                    `MOBIEL` varchar(255) DEFAULT NULL,
                    `NOODNUMMER` varchar(255) DEFAULT NULL,
                    `EMAIL` varchar(45) DEFAULT NULL,
                    `LIDNR` varchar(10) DEFAULT NULL,
					`LIDTYPE_ID` mediumint UNSIGNED NOT NULL,
					`ZUSTERCLUB_ID` mediumint UNSIGNED DEFAULT NULL,
                    `LIERIST` tinyint UNSIGNED NOT NULL DEFAULT 0,
                    `STARTLEIDER` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`INSTRUCTEUR` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`CIMT` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`DDWV_CREW` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`DDWV_BEHEERDER` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`BEHEERDER` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`STARTTOREN` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`ROOSTER` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`CLUBBLAD_POST` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`MEDICAL` date DEFAULT NULL,
					`GEBOORTE_DATUM` date DEFAULT NULL,
                    `INLOGNAAM` varchar(45) DEFAULT NULL,
					`WACHTWOORD` varchar(255) DEFAULT NULL,    
					`SECRET` varchar(255) DEFAULT NULL,  
					`AUTH` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`AVATAR` varchar(255) DEFAULT NULL,        
                    `HEEFT_BETAALD` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`PRIVACY` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`BEPERKINGEN` text DEFAULT NULL,
					`OPMERKINGEN` text DEFAULT NULL,
                    `VERWIJDERD` tinyint UNSIGNED NOT NULL DEFAULT 0,
					`LAATSTE_AANPASSING` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

					CONSTRAINT ID_PK PRIMARY KEY (ID),
						INDEX (`NAAM`),
						INDEX (`LIDTYPE_ID`),
						INDEX (`STARTLEIDER`),
						INDEX (`INSTRUCTEUR`),
						INDEX (`LIERIST`),
						INDEX (`VERWIJDERD`),

						FOREIGN KEY (LIDTYPE_ID) REFERENCES ref_types(ID),
						FOREIGN KEY (ZUSTERCLUB_ID) REFERENCES ref_leden(ID) 				
				)", $this->dbTable);
			parent::DbUitvoeren($query);

			if (isset($FillData))
			{
				$query = sprintf("
					INSERT INTO 
						`%s` 
							(`ID`,  
							`NAAM`,
							`VOORNAAM`, 
							`TUSSENVOEGSEL`, 
							`ACHTERNAAM`, 
							`ADRES`, 
							`POSTCODE`, 
							`WOONPLAATS`, 
							`LIDNR`, 
							`LIDTYPE_ID`, 
							`MOBIEL`, 
							`EMAIL`, 
                            `NOODNUMMER`, 
                            `TELEFOON`, 
							`INSTRUCTEUR`, 
							`DDWV_CREW`, 
                            `STARTLEIDER`, 
                            `HEEFT_BETAALD`, 
                            `LIERIST`) 
						VALUES
							('1','Beheerder', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 600 ,NULL, NULL, NULL, NULL, '0', '0','0','0','0'),
							('2','Zusterclub', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 607 ,NULL, NULL, NULL, NULL, '0', '0','0','0','0'),
                            ('10855','Truus de Mier', 'Truus', 'de', 'Mier', 'Boompje 72', '2211 AA', 'Puthoek', '12091', 625,NULL,'mier@fabeltje.com', NULL, NULL, '0', '0','0','1','0'),
                            ('10213','Teun Stier', 'Teun', NULL, 'Stier', 'Weide 1', '7311 AA', 'De Veenen', '10022', 602, NULL, '06-1256770','stier@fabeltje.com','06-1256770', '0','1','1','1','0'),
                            ('10858','Meneer de Uil', 'Meneer', NULL, 'Uil', 'Kerkstraat 42', '1299 AA', 'Heuven', '41382', 601,'06-5112006', NULL, '0882-10111','0310-430210', '0','0','0','1','1'),
                            ('10408','Meindert het Paard', 'Meindert', 'het', 'Paard', 'Hoefijzer 3', '8311 QR', 'Heidekant', '11139',603,'06-1025500','meindert@fabeltje.com','0112-11801','086-1506822','1', '0','1','0','1'),
                            ('10804','Willem Bever', 'Willem', NULL, 'Bever', 'Linge 66', '7412 BA', 'Dunsborg', '588139', 602,'06-2828281','willem@fabeltje.com', NULL, NULL, '0', '0','0','1','1'),
                            ('10632','Gerrit de Postduif', 'Gerrit', 'de', 'Postduif', 'Sperwerweg 57', '1623 VT', 'Zwiep', '11140',602,'06-10285005','g.de.postduif@fabeltje.com','0319-18348','0278-20000', '0','0','0','1','0'),
                            ('10063','Isadora Paradijsvogel', 'Isadora', NULL, 'Paradijsvogel', 'Vogellaarstraat 1a', '5407 KR', 'Kulsdom', '91900',606,'06-10005001','isadora.paradijsvogel@fabeltje.com','0112-99820','0818-71100', '0','0','0','1','0'),
                            ('10265','Lowieke de Vos','Lowieke', NULL, 'Vos', 'Kattenstaart 18', '9277 YT', 'Zuidloo', '12239',602,'06-10333005','vos@fabeltje.com','0112-10008','020-120333', '0','0','0','1','0'),
                            ('10395','Juffrouw Ooievaar', 'Juffrouw', NULL, 'Ooievaar', 'Torenspits 18', '1231 JG', 'Tjoene', '22154',608,'06-2011301','ooievaar@fabeltje.com', NULL, '070-120311', '0','0','0','0','0'),
                            ('10115','Momfer de Mol', 'Momfer', 'de', 'Mol', 'Trompetdreef 31', '7812 UI', 'Hanendorp', '93562',625,'06-2009710','m.de.mol@fabeltje.com', NULL, '020-200120','1', '0','0','1','0'),
                            ('10470','Bor de Wolf','Bor', 'de', 'Wolf', 'Wolga 11', '7630 EE', 'Niersen', '11511',602,'06-1119220','wolf@fabeltje.com', NULL, '027-120887','1', '0','0','1','0'),
							('10001','Zoef de Haas', 'Zoef', 'de', 'Haas', 'Ringlaan 99', '1056 AI', 'Assel', '31313',603,'06-6119330','zoefzoef@fabeltje.com', NULL, '027-120827','1', '0','1','1','0');
					", $this->dbTable);
				parent::DbUitvoeren($query);	
				
				// maak gebruiker beheerder beheerder
				$query = sprintf("UPDATE `%s` SET `BEHEERDER` = 1 WHERE ID = 1", $this->dbTable);
				parent::DbUitvoeren($query);

				// toevoegen zusterclub
				$query = sprintf("UPDATE `%s` SET `ZUSTERCLUB_ID` = 2 WHERE ID = 10115", $this->dbTable);
				parent::DbUitvoeren($query);

				// voeg login informatie toe om te kunnen testen
				$login_info = array();
				$login_info['1']     = (object) ['INLOGNAAM' => 'beheer', 'WACHTWOORD' => sha1(strtolower ('beheer') . '@dm1nAcc0unt')];	// beheerder
				$login_info['10115'] = (object) ['INLOGNAAM' => 'momfer', 'WACHTWOORD' => sha1(strtolower ('momfer') . 'mol')];				// Momfer de mol als DDWV'er
				$login_info['10001'] = (object) ['INLOGNAAM' => 'zoef',   'WACHTWOORD' => sha1(strtolower ('zoef')   . 'haas')];			// Zoef de haas als instructeur
				$login_info['10213'] = (object) ['INLOGNAAM' => 'teun',   'WACHTWOORD' => sha1(strtolower ('teun')   . 'stier')];			// Teun stier als DDWV crew
				$login_info['10632'] = (object) ['INLOGNAAM' => 'gerrit', 'WACHTWOORD' => sha1(strtolower ('gerrit') . 'postduif')];		// Gerrit de Postduif als lid

				foreach ($login_info as $ID => $credentials) 
				{
					$query = sprintf("UPDATE `%s` SET `INLOGNAAM` = '%s', `WACHTWOORD` = '%s'  WHERE ID = %s", $this->dbTable, $credentials->INLOGNAAM, $credentials->WACHTWOORD, $ID);
					parent::DbUitvoeren($query);	
				}

				$query = sprintf("UPDATE `%s` SET `OPMERKINGEN` = 'DEMO Account' WHERE ID >= 0", $this->dbTable);
				parent::DbUitvoeren($query);
			}
		}

		/*
		Maak database views, als view al bestaat wordt deze overschreven
		*/		
		function CreateViews()
		{
			$l = MaakObject('Login');
			if ($l->isInstaller() == false)
				throw new Exception("401;Geen installer;");

			parent::DbUitvoeren("DROP VIEW IF EXISTS leden_view");
			$query =  sprintf("CREATE VIEW `leden_view` AS
				SELECT 
					l.*,
					`t`.`OMSCHRIJVING` AS `LIDTYPE`,
					`z`.`NAAM` AS `ZUSTERCLUB`
				FROM
					`%s` `l`    
					LEFT JOIN `ref_types` `t` ON (`l`.`LIDTYPE_ID` = `t`.`ID`)
					LEFT JOIN `ref_leden` `z` ON (`l`.`ZUSTERCLUB_ID` = `z`.`ID`)
				WHERE
					`l`.`VERWIJDERD` = 0  
				ORDER BY 
					ACHTERNAAM, VOORNAAM;", $this->dbTable);
								
			parent::DbUitvoeren($query);
		}

		/*
		Haal een enkel record op uit de database
		*/
		function GetObject($ID,  $heeftVerwijderd = true)
		{
			Debug(__FILE__, __LINE__, sprintf("Leden.GetObject(%s)", $ID));	

			if ($ID == null)
				throw new Exception("406;Geen ID in aanroep;");

			$conditie = array();
			$conditie['ID'] = isINT($ID, "ID");
			
			// ophalen mag alleen door ingelogde gebruiker of beheerder
			$l = MaakObject('Login');
			if ($l->getUserFromSession() != $ID)
			{
				if (($l->isBeheerder() == false) && ($l->isBeheerderDDWV() == false))
					throw new Exception("401;Geen leesrechten;");
			}

			if ($heeftVerwijderd == false)
				$conditie['VERWIJDERD'] = 0;		// Dus geen verwijderd record

			$obj = parent::GetSingleObject($conditie);
			if ($obj == null)
				throw new Exception("404;Record niet gevonden;");
			
			// privacy maskering
			$obj = $this->privacyMask($obj);
			return $obj;				
		}

		/*
		Haal een enkel record op uit de database
		*/
		function GetObjectByLidnr($LidNr)
		{
			Debug(__FILE__, __LINE__, sprintf("Leden.GetObjectByLidnr(%s)", $LidNr));	

			if ($LidNr == null)
				throw new Exception("406;Geen LidNr in aanroep;");

			$conditie = array();
			$conditie['LIDNR'] = isINT($LidNr, "LIDNR");
			$conditie['VERWIJDERD'] = 0;		

			$obj = parent::GetSingleObject($conditie);			

			if ($obj == null)
				throw new Exception("404;Record niet gevonden;");
			
			return $obj;				
		}
			
		/*
		Vraag lid record op basis van de login naam, login naam is dus uniek
		*/
		function GetObjectByLoginNaam($loginNaam)
		{
			Debug(__FILE__, __LINE__, sprintf("Leden.GetObjectByLoginNaam(%s)", $loginNaam));	

			if ($loginNaam == null)
				throw new Exception("406;Geen login naam in aanroep;");

			$conditie = array();
			$conditie['INLOGNAAM'] = $loginNaam ;
			$conditie['VERWIJDERD'] = 0;		

			$obj = parent::GetSingleObject($conditie);			

			if ($obj == null)
				throw new Exception("404;Record niet gevonden;");
							
			return $obj;				
		}

		/*
		Haal een dataset op met records als een array uit de database. 
		*/		
		function GetObjects($params)
		{
			$functie = "Leden.GetObjects";
			Debug(__FILE__, __LINE__, sprintf("%s(%s)", $functie, print_r($params, true)));		
			
			$where = ' WHERE 1=1 ';
			$orderby = "";
			$alleenLaatsteAanpassing = false;
			$limit = -1;
			$start = -1;
			$velden = "*";
			$query_params = array();

			$l = MaakObject('Login');
			if ($l->isDDWV())
			{
				Debug(__FILE__, __LINE__, sprintf("%s: %s is DDWV'er, beperk query", $functie, $l->getUserFromSession()));
				$where .= " AND ((LIDTYPE_ID = 625) OR (DDWV_CREW = '1')) ";
			}

			foreach ($params as $key => $value)
			{
				switch ($key)
				{
					case "ID" : 
						{
							$id = isINT($value, "ID");
							$where .= " AND ID=?";
							array_push($query_params, $id);

							Debug(__FILE__, __LINE__, sprintf("%s: ID='%s'", $functie, $id));
							break;
						}					
					case "LAATSTE_AANPASSING" : 
						{
							$alleenLaatsteAanpassing = isBOOL($value, "LAATSTE_AANPASSING");

							Debug(__FILE__, __LINE__, sprintf("%s: LAATSTE_AANPASSING='%s'", $functie, $alleenLaatsteAanpassing));
							break;
						}	
					case "VELDEN" : 	
						{
							if (strpos($value,';') !== false)
								throw new Exception("405;VELDEN is onjuist;");

							$velden = $value;
							Debug(__FILE__, __LINE__, sprintf("%s: VELDEN='%s'", $functie, $velden));
							break;
						}											
					case "SORT" : 
						{
							if (strpos($value,';') !== false)
								throw new Exception("405;SORT is onjuist;");
							
							$orderby = sprintf(" ORDER BY %s ", $value);
							Debug(__FILE__, __LINE__, sprintf("%s: SORT='%s'", $functie, $value));
							break;
						}
					case "START" : 
						{
							$s = isINT($value, "START");
							
							if ($s < 0)
								throw new Exception("405;START groter of gelijk zijn dan 0;");
							
							$start = $s;

							Debug(__FILE__, __LINE__, sprintf("%s: START='%s'", $functie, $start));
							break;
						}	
					case "MAX" : 
						{
							$max = isINT($value, "MAX");

							if ($max > 0)
							{
								$limit = $max;
								Debug(__FILE__, __LINE__, sprintf("%s: LIMIT='%s'", $functie, $limit));
							}
							break;
						}					
					case "SELECTIE" : 
						{
							$where .= " AND ((NAAM LIKE ?) ";
							$where .= "  OR (EMAIL LIKE ?) ";
							$where .= "  OR (TELEFOON LIKE ?) ";
							$where .= "  OR (MOBIEL LIKE ?) ";
							$where .= "  OR (NOODNUMMER LIKE ?)) ";

							$s = "%" . trim($value) . "%";
							$query_params = array($s, $s, $s, $s, $s);

							Debug(__FILE__, __LINE__, sprintf("%s: SELECTIE='%s'", $functie, $s));	
							break;
						}
					case "TYPES" : 						
						{
							if ($l->isDDWV()) 
							{
								Debug(__FILE__, __LINE__, sprintf("%s: Gebruiker is DDWV'er, lidtype kan dan niet gespecifieerd worden", $functie, $limit));
							}
							else
							{
								isCSV($value, "TYPES");
								$where .= sprintf(" AND LIDTYPE_ID IN(%s)", trim($value));
	
								Debug(__FILE__, __LINE__, sprintf("%s: TYPES='%s'", $functie, $value));
							}
							break;
						}	
					case "CLUBLEDEN" : 
						{
							$alleenLeden = isBOOL($value, "CLUBLEDEN");

							if ($alleenLeden)
								$where .= sprintf(" AND LIDTYPE_ID > 600 AND LIDTYPE_ID <= 606 ");

							Debug(__FILE__, __LINE__, sprintf("%s: CLUBLEDEN='%s'", $functie, $alleenLeden));
							break;
						}	
					case "INSTRUCTEURS" : 
						{
							$alleenInstructeur = isBOOL($value, "INSTRUCTEURS");

							if ($alleenInstructeur)
								$where .= sprintf(" AND INSTRUCTEUR = 1 ");

							Debug(__FILE__, __LINE__, sprintf("%s: INSTRUCTEURS='%s'", $functie, $alleenInstructeur));
							break;
						}	
					case "DDWV_CREW" : 
						{
							$alleenDDWV = isBOOL($value, "DDWV_CREW");

							if ($alleenDDWV)
								$where .= sprintf(" AND DDWV_CREW = 1 ");

							Debug(__FILE__, __LINE__, sprintf("%s: DDWV_CREW='%s'", $functie, $alleenDDWV));
							break;
						}							
					case "LIERISTEN" : 
						{
							$alleenLieristen = isBOOL($value, "LIERISTEN");

							if ($alleenLieristen)
								$where .= sprintf(" AND LIERIST = 1 ");

							Debug(__FILE__, __LINE__, sprintf("%s: LIERISTEN='%s'", $functie, $alleenLieristen));
							break;
						}	
					case "STARTLEIDERS" : 
						{
							$alleenStartleiders = isBOOL($value, "STARTLEIDERS");

							if ($alleenStartleiders)
								$where .= sprintf(" AND STARTLEIDER = 1 ");

							Debug(__FILE__, __LINE__, sprintf("%s: STARTLEIDERS='%s'", $functie, $alleenStartleiders));
							break;
						}							
					default:
						{
							throw new Exception(sprintf("405;%s is een onjuiste parameter;", $key));
							break;
						}																																					
				}
			}
				
			$query = "
				SELECT 
					%s
				FROM
					`leden_view`" . $where . $orderby;
			
			$retVal = array();

			$retVal['totaal'] = $this->Count($query, $query_params);		// totaal aantal of record in de database
			$retVal['laatste_aanpassing']=  $this->LaatsteAanpassing($query, $query_params);
			Debug(__FILE__, __LINE__, sprintf("TOTAAL=%d, LAATSTE_AANPASSING=%s", $retVal['totaal'], $retVal['laatste_aanpassing']));	

			if ($alleenLaatsteAanpassing)
			{
				$retVal['dataset'] = null;
				return $retVal;
			}
			else
			{			
				if ($limit > 0)
				{
					if ($start < 0)				// Is niet meegegeven, dus start op 0
						$start = 0;

					$query .= sprintf(" LIMIT %d , %d ", $start, $limit);
				}			
				$rquery = sprintf($query, $velden);
				parent::DbOpvraag($rquery, $query_params);
				$retVal['dataset'] = parent::DbData();

				for ($i = 0; $i < count($retVal['dataset']) ; $i++) 
				{
					// privacy maskering
					$retVal['dataset'][$i] = $this->privacyMask($retVal['dataset'][$i]);	
				}
				return $retVal;
			}
			return null;  // Hier komen we nooit :-)
		}	

		/*
		Markeer een record in de database als verwijderd. Het record wordt niet fysiek verwijderd om er een link kan zijn naar andere tabellen.
		Het veld VERWIJDERD wordt op "1" gezet.
		*/
		function VerwijderObject($id, $verificatie = true)
		{
			Debug(__FILE__, __LINE__, sprintf("Leden.VerwijderObject(%s, %s)", $id, $verificatie));

			$l = MaakObject('Login');
			if ($l->magSchrijven() == false)
				throw new Exception("401;Geen schrijfrechten;");

			if ($id == null)
				throw new Exception("406;Geen ID in aanroep;");
			
			isCSV($id, "ID");
			parent::MarkeerAlsVerwijderd($id, $verificatie);
		}		
		
		/*
		Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
		*/		
		function AddObject($LidData)
		{
			Debug(__FILE__, __LINE__, sprintf("Leden.AddObject(%s)", print_r($LidData, true)));
			
			// schrijven mag alleen door beheerder
			$l = MaakObject('Login');
			if (($l->isBeheerder() == false) && ($l->isBeheerderDDWV() == false))
			{
				throw new Exception("401;Geen schrijfrechten;");
			}
			
			if ($LidData == null)
				throw new Exception("406;Lid data moet ingevuld zijn;");			

			if (array_key_exists('ID', $LidData))
			{
				$id = isINT($LidData['ID'], "ID");
							
				// ID is opgegeven, maar bestaat record?
				try 	// Als record niet bestaat, krijgen we een exception
				{	
					$this->GetObject($id);
				}
				catch (Exception $e) {}	

				if (parent::NumRows() > 0)
					throw new Exception(sprintf("409;Record met ID=%s bestaat al;", $id));									
			}
			
			// Voorkom het dezelfde lidnr meerdere keren voorkomt in de tabel
			if (array_key_exists('LIDNR', $LidData))
			{
				if ($LidData['LIDNR'] != null)	// null is altijd goed
				{
					try 	// Als record niet bestaat, krijgen we een exception
					{				
						$this->GetObjectByLidnr($LidData['LIDNR']);
					}
					catch (Exception $e) {}	

					if (parent::NumRows() > 0)
						throw new Exception(sprintf("409;Record met LIDNR=%s bestaat al;", $LidData['LIDNR']));
				}
			}

			// Voorkom het dezelfde login meerdere keren voorkomt in de tabel
			if (array_key_exists('INLOGNAAM', $LidData))
			{
				if ($LidData['INLOGNAAM'] != null)	// null is altijd goed
				{
					try 	// Als record niet bestaat, krijgen we een exception
					{				
						$this->GetObjectByLoginNaam($LidData['INLOGNAAM']);
					}
					catch (Exception $e) {}	

					if (parent::NumRows() > 0)
						throw new Exception(sprintf("409;Record met INLOGNAAM=%s bestaat al;", $LidData['INLOGNAAM']));
				}			
			}

			if ((!array_key_exists('NAAM', $LidData)) && (!array_key_exists('ACHTERNAAM', $LidData)))
				throw new Exception("406;NAAM is verplicht;");
			
			if (!array_key_exists('LIDTYPE_ID', $LidData))
				throw new Exception("406;Lidtype is verplicht;");
				
			// Neem data over uit aanvraag
			$l = $this->RequestToRecord($LidData);
						
			$id = parent::DbToevoegen($l);
			Debug(__FILE__, __LINE__, sprintf("lid toegevoegd id=%d", $id));

			$lid = $this->GetObject($id);

			if ($lid['SECRET'] == null)
				$this->SetSecret($id);

			return $lid;
		}

		/*
		Toevoegen van een record. Het is niet noodzakelijk om alle velden op te nemen in het verzoek
		*/		
		function UpdateObject($LidData)
		{
			Debug(__FILE__, __LINE__, sprintf("Leden.UpdateObject(%s)", print_r($LidData, true)));
			
			// schrijven mag alleen door ingelogde gebruiker of beheerder
			$l = MaakObject('Login');
			if ($l->getUserFromSession() != $LidData["ID"])
			{
				if (($l->isBeheerder() == false) && ($l->isBeheerderDDWV() == false))
				{
					throw new Exception("401;Geen schrijfrechten;");
				}
			}

			if ($LidData == null)
				throw new Exception("406;Lid data moet ingevuld zijn;");			

			if (!array_key_exists('ID', $LidData))
				throw new Exception("406;ID moet ingevuld zijn;");

			$id = isINT($LidData['ID'], "ID");
			
            // Voorkom dat datum meerdere keren voorkomt in de tabel
			if (array_key_exists('LIDNR', $LidData))
			{
				if ($LidData['LIDNR'] != null)	// null is altijd goed
				{
					try 	// Als record niet bestaat, krijgen we een exception
					{
						$l = $this->GetObjectByLidnr($LidData['LIDNR']);
					}
					catch (Exception $e) {}

					if (parent::NumRows() > 0)
					{
						if ($id != $l['ID'])
							throw new Exception("409;LidNr bestaat reeds;");
					}	
				}
			}

            // Voorkom het dezelfde login meerdere keren voorkomt in de tabel
			if (array_key_exists('INLOGNAAM', $LidData))
			{
				if ($LidData['INLOGNAAM'] != null)	// null is altijd goed
				{
					try 	// Als record niet bestaat, krijgen we een exception
					{
						$l = $this->GetObjectByLoginNaam($LidData['INLOGNAAM']);
					}
					catch (Exception $e) {}

					if (parent::NumRows() > 0)
					{
						if ($id != $l['ID'])
							throw new Exception("409;Inlognaam bestaat reeds;");
					}	
				}
			}			

			// Neem data over uit aanvraag
			$l = $this->RequestToRecord($LidData);

			parent::DbAanpassen($id, $l);
			if (parent::NumRows() === 0)
				throw new Exception("404;Record niet gevonden;");				
			
			$lid = $this->GetObject($id);

			if ($lid['SECRET'] == null)
				$this->SetSecret($id);

			return $lid;
		}		

		/*
		Upload van een avatar
		*/
		function UploadAvatar($id, $file)
		{
			Debug(__FILE__, __LINE__, sprintf("Leden.UploadAvatar(%s)", $id));
			
			// schrijven mag alleen door ingelogde gebruiker of beheerder
			$l = MaakObject('Login');
			if ($l->getUserFromSession() != $LidData["ID"])
			{
				if (($l->isBeheerder() == false) && ($l->isBeheerderDDWV() == false))
					throw new Exception("401;Geen schrijfrechten;");
			}

			$id = isINT($id, "ID");
			
			// geeft een exceptie als id niet bestaat
			$lid = $this->GetObject($id);

			if ($file->getError() !== UPLOAD_ERR_OK) {
				Debug(__FILE__, __LINE__, "Leden.UploadAvatar upload mislukt");
				throw new Exception("500;Upload mislukt;");		
			}

			$upload_dir = "avatars";
			$ext_type = array('gif','jpg','jpe','jpeg','png');
			$extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);

			if (!in_array(strtolower($extension), $ext_type)) {
				Debug(__FILE__, __LINE__, sprintf("Leden.UploadAvatar extentie %s is ongeldig)", $id));
				throw new Exception("422;Onjuiste bestand extentie;");	
			}	

			$r = date('YmdHis'); // random is nodig om uniek bestandsnaam te krijgen
			$target = sprintf("%s-%s-%s", $id, $lid['INLOGNAAM'], $r);	
			$file->moveTo(sprintf("./%s/%s.%s", $upload_dir, $target, $extension));
			$url =  'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://{$_SERVER['HTTP_HOST']}";

			$upd['AVATAR'] = sprintf("%s/%s/%s.%s", $url, $upload_dir, $target, $extension);
			parent::DbAanpassen($id, $upd);

			Debug(__FILE__, __LINE__, sprintf("Leden.UploadAvatar image url= %s", $upd['AVATAR']));
			return $upd['AVATAR'] ;
		}

		/*
		Zet secret key voor google authenticator
		*/
		function SetSecret($id)
		{
			$ga = new PHPGangsta_GoogleAuthenticator();
			$secret = $ga->createSecret();

			$query = sprintf("UPDATE `%s` SET `SECRET` = '%s'  WHERE ID = %s", $this->dbTable , $secret, $id);
			parent::DbUitvoeren($query);	
		}

		/*
		Copieer data van request naar velden van het record 
		*/
		function RequestToRecord($input)
		{
			$record = array();
			$l = MaakObject('Login');

			$field = 'ID';
			if (array_key_exists($field, $input))
				$record[$field] = isINT($input[$field], $field);

			if (array_key_exists('NAAM', $input)) {
				$record['NAAM'] = $input['NAAM']; 
			}
			else {
				$record['NAAM'] = "";

				if (array_key_exists('VOORNAAM', $input))
					$record['NAAM'] .= $input['VOORNAAM'] . " "; 

				if (array_key_exists('TUSSENVOEGSEL', $input))
					$record['NAAM'] .= $input['TUSSENVOEGSEL'] . " "; 

				if (array_key_exists('ACHTERNAAM', $input))	
					$record['NAAM'] .= $input['ACHTERNAAM']; 	
			}

			if (array_key_exists('VOORNAAM', $input))
				$record['VOORNAAM'] = $input['VOORNAAM']; 

			if (array_key_exists('TUSSENVOEGSEL', $input))
				$record['TUSSENVOEGSEL'] = $input['TUSSENVOEGSEL']; 

			if (array_key_exists('ACHTERNAAM', $input))
				$record['ACHTERNAAM'] = $input['ACHTERNAAM']; 

			if (array_key_exists('ADRES', $input))
				$record['ADRES'] = $input['ADRES']; 	
				
			if (array_key_exists('POSTCODE', $input))
				$record['POSTCODE'] = $input['POSTCODE']; 	
				
			if (array_key_exists('WOONPLAATS', $input))
				$record['WOONPLAATS'] = $input['WOONPLAATS']; 				

			if (array_key_exists('TELEFOON', $input))
				$record['TELEFOON'] = $input['TELEFOON']; 

			if (array_key_exists('MOBIEL', $input))
				$record['MOBIEL'] = $input['MOBIEL']; 

			if (array_key_exists('NOODNUMMER', $input))
				$record['NOODNUMMER'] = $input['NOODNUMMER']; 

			if (array_key_exists('EMAIL', $input))
				$record['EMAIL'] = $input['EMAIL']; 

			$field = 'CLUBBLAD_POST';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			$field = 'GEBOORTE_DATUM';
			if (array_key_exists($field, $input))
				$record[$field]= isDATE($input[$field], $field, true);	

			$field = 'MEDICAL';
			if (array_key_exists($field, $input))
				$record[$field]= isDATE($input[$field], $field, true);		

			if (array_key_exists('INLOGNAAM', $input))
				$record['INLOGNAAM'] = $input['INLOGNAAM']; 	

			$ld = $l->lidData();
			if (array_key_exists('WACHTWOORD', $input))
			{
				if (strlen($input['WACHTWOORD']) > 0)
				{
					$loginnaam = (isset($record['INLOGNAAM'])) ? $record['INLOGNAAM'] : $ld->INLOGNAAM;
					$record['WACHTWOORD'] = sha1(strtolower ($loginnaam) . $input['WACHTWOORD']);
				}
			}

			$field = 'PRIVACY';
			if (array_key_exists($field, $input))
				$record[$field] = isBOOL($input[$field], $field);

			if (array_key_exists('OPMERKINGEN', $input))
				$record['OPMERKINGEN'] = $input['OPMERKINGEN'];	

			// onderstaande velden zijn beperkt aanpasbaar
			if (($l->isBeheerder()) || ($l->isBeheerderDDWV()))
			{
				$field = 'DDWV_CREW';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);		

				$field = 'ZUSTERCLUB_ID';
				if (array_key_exists($field, $input))
					$record[$field] = isINT($input[$field], $field, true, 'Leden');
			}

			if (($l->isBeheerder()) || ($l->isRooster()))
			{
				$field = 'LIERIST';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);

				$field = 'STARTLEIDER';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);			
			}

			if (($l->isBeheerder()) || ($l->isCIMT()))
			{
				$field = 'INSTRUCTEUR';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);
			}
			
			if ($l->isBeheerder() == true)
			{
				$field = 'ID';
				if (array_key_exists($field, $input))
					$record[$field] = isINT($input[$field], $field);
	
				$field = 'LIDNR';
				if (array_key_exists($field, $input))
					$record[$field] = $input[$field];				

				$field = 'LIDTYPE_ID';
				if (array_key_exists($field, $input))
					$record[$field] = isINT($input[$field], $field, true, "Types");

				$field = 'CIMT';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);

				$field = 'ROOSTER';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);

				$field = 'STARTTOREN';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);

				$field = 'DDWV_BEHEERDER';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);

				$field = 'BEHEERDER';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);

				$field = 'AUTH';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);

				$field = 'HEEFT_BETAALD';
				if (array_key_exists($field, $input))
					$record[$field] = isBOOL($input[$field], $field);

				if (array_key_exists('BEPERKINGEN', $input))
					$record['BEPERKINGEN'] = $input['BEPERKINGEN']; 	
			}

			if (array_key_exists('AVATAR', $input))
				throw new Exception("405;AVATAR kan niet extern gezet worden;");
					
			return $record;				
		}
	

		function isPermissie($key, $id = null, $lid = null)
		{
			$functie = "Leden.isPermissie";
			Debug(__FILE__, __LINE__, sprintf("%s(%s, %s, %s)", $functie, $key, $id, print_r($lid, true)));	

			$retVal = false;

			if (($id == null) && ($lid == null)) {
				Debug(__FILE__, __LINE__, sprintf("%s: ERROR id & lid zijn NULL, check source code", $functie));
				return false;
			}
			
			if ($lid == null) 
			{
				try
				{
					$lid = $this->GetObject($id);
				}
				catch(Exception $exception) { return false; }
			}
			
			if ($lid[$key] == "1") 
				$retVal = true;
				
			Debug(__FILE__, __LINE__, sprintf("Lid=%s isPermissie %s=%s", $lid['NAAM'], $key, ($retVal) ? "true" : "false"));
			return $retVal;				
		}
	
		
		function isStartleider($id = null, $lid = null, $datum = null)
		{
			$functie = "Leden.isStartleider";
			Debug(__FILE__, __LINE__, sprintf("%s(%s, %s, %s)", $functie, $id, print_r($lid, true), $datum));	
			
			if ($this->isPermissie("STARTLEIDER", $id, $lid) == false)
				return false;			

			if ($datum == null) $datum = date('Y-m-d');

			if ($lid == null) 
			{
				try
				{
					$lid = $this->GetObject($id);
				}
				catch(Exception $exception) { return false; }
			}

			$di = MaakObject('Daginfo');	
			try
			{					
				$diObj = $di->GetObject(false, $datum);

				if (isset($diObj[0]['OCHTEND_STARTLEIDER']))
				{
					if ($lid['ID'] == $diObj[0]['OCHTEND_STARTLEIDER']) 
					{
						Debug(__FILE__, __LINE__, sprintf("%s: %s(%d) is ochtend startleider, datum=%s, return true", $functie, $lid['NAAM'], $lid['ID'], $datum ));
						return true;
					}
				}
				if (isset($diObj[0]['MIDDAG_STARTLEIDER']))
				{
					if ($lid['ID']  == $diObj[0]['MIDDAG_STARTLEIDER'])
					{
						Debug(__FILE__, __LINE__, sprintf("%s: %s(%d) is middag startleider, datum=%s, return true", $functie, $lid['NAAM'], $lid['ID'], $datum ));
						return true;
					}
				}			
				
				$rooster = MaakObject('Rooster');				
				$roosterObj = $rooster->GetObject($datum);		

				if (isset($roosterObj[0]['OCHTEND_STARTLEIDER']))
				{
					if ($lid['ID'] == $roosterObj[0]['OCHTEND_STARTLEIDER']) 
					{
						Debug(__FILE__, __LINE__, sprintf("%s: %s(%d) is ochtend startleider op rooster, datum=%s, return true", $functie, $lid['NAAM'], $lid['ID'], $datum ));
						return true;
					}
				}
				if (isset($roosterObj[0]['MIDDAG_STARTLEIDER']))
				{
					if ($lid['ID']  == $roosterObj[0]['MIDDAG_STARTLEIDER'])
					{
						Debug(__FILE__, __LINE__, sprintf("%s: %s(%d) is middag startleider op rooster, datum=%s, return true", $functie, $lid['NAAM'], $lid['ID'], $datum ));
						return true;
					}
				}
			}
			catch(Exception $exception) {}

			return false;
		}
	
		function isClubVlieger($id = null, $lid = null)
		{
			$functie = "Leden.isClubVlieger";
			Debug(__FILE__, __LINE__, sprintf("%s(%s, %s)", $functie, $id, print_r($lid, true)));	
			$retVal = false;
			
			if (($id == null) && ($lid == null)) {
				Debug(__FILE__, __LINE__, sprintf("%s: ERROR id & lid zijn NULL, check source code", $functie));
				return false;
			}

			if ($lid == null) 
			{
				try
				{
					$lid = $this->GetObject($id);
				}
				catch(Exception $exception) { return false; }
			}
			
			if (($lid['LIDTYPE_ID'] == "601") ||       // 601 = Erelid
				($lid['LIDTYPE_ID'] == "602") ||       // 602 = Lid 
				($lid['LIDTYPE_ID'] == "603") ||       // 603 = Jeugdlid 
				($lid['LIDTYPE_ID'] == "606"))         // 606 = Donateur
				$retVal = true;
				
			Debug(__FILE__, __LINE__, sprintf("LIDTYPE_ID=%s isVliegendLid=%s", $lid['LIDTYPE_ID'], $retVal ? "true" : "false"));
			return $retVal;			
		}

		function isDDWV($id = null, $lid = null)
		{
			$functie = "Leden.isDDWV";
			Debug(__FILE__, __LINE__, sprintf("%s(%s, %s)", $functie, $id, print_r($lid, true)));	
			$retVal = false;
			
			if (($id == null) && ($lid == null)) {
				Debug(__FILE__, __LINE__, sprintf("%s: ERROR id & lid zijn NULL, check source code", $functie));
				return false;
			}
			
			if ($lid == null) 
			{
				try
				{
					$lid = $this->GetObject($id);
				}
				catch(Exception $exception) { return false; }
			}
			
			if ($lid['LIDTYPE_ID'] == "625")        // 625 = DDWV
				$retVal = true;
				
			Debug(__FILE__, __LINE__, sprintf("LIDTYPE_ID=%s isDDWV=%s", $lid['LIDTYPE_ID'], $retVal ? "true" : "false"));
			return $retVal;				
		}						

		// privacy maskering
		function privacyMask($lid)
		{
			global $app_settings;

			$l = MaakObject('Login');
			if (array_key_exists("WACHTWOORD", $lid))
				$lid['WACHTWOORD'] 	= "****";

			if ($lid['SECRET'] != null) {
				if (($l->isBeheerder() === true) ||
					($l->isBeheerderDDWV() === true)) 
				{
					$ga = new PHPGangsta_GoogleAuthenticator();
					$lid['SECRET'] = $ga->getQRCodeGoogleUrl($lid['INLOGNAAM'], $lid['SECRET'], $app_settings['Vereniging']);
				}				
				else
				{
					$lid['SECRET'] 	= "****";
				}
			}


			if ($lid['ID'] !== $l->getUserFromSession())
			{

				if (($l->isBeheerder() === false) &&
					($l->isBeheerderDDWV() === false) &&
					($l->isInstructeur() === false) &&
					($lid['PRIVACY'] === "1"))
				{
					$lid['TELEFOON'] 	= "****";
					$lid['MOBIEL'] 		= "****";
					$lid['ADRES'] 		= "****";
					$lid['POSTCODE'] 	= "****";
					$lid['WOONPLAATS'] 	= "****";
					$lid['MEDICAL'] 	= "****";
				}
			}
			return $lid;
		}
	}	
?>