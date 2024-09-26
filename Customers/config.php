<?php

	// $DB_HOST = '127.0.0.1:3306';
	// $DB_USER = 'u473175646_cmlpaint';
	// $DB_PASS = '6Vk~LBYc';
	// $DB_NAME = 'u473175646_edgedata';

		// $DB_HOST = 'localhost';
		// $DB_USER = 'u736664699_123';
		// $DB_PASS = 'Cmlpaint2024';
		// $DB_NAME = 'u736664699_123';

		$DB_HOST = 'localhost';
		$DB_USER = 'root';
		$DB_PASS = '';
		$DB_NAME = 'cml_paint_db';
	
	try{
		$DB_con = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME}",$DB_USER,$DB_PASS);
		$DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOException $e){
		echo $e->getMessage();
	}
	
