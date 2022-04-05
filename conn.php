<?php
	

	$host = $_SERVER['HTTP_HOST'] ; 


	switch ( $host )
	{

		default : 
			$G_DB = [ 
				'host' => 'ls-e6eb61010c430fb96846bacebaac754301cb5738.c0pxofply1yr.eu-west-3.rds.amazonaws.com' , 
				'dbname' 	=> 'gco' , 
				'user' 		=> 'dbmasteruser' , 
				'password' 	=> '[_(ze&v&cZd^4YM$&&F$r~Pu3oy84+]u' , 
				'host_api' 	=> 'http://13.38.107.82/api/' , 
				'root' 		=> $_SERVER['DOCUMENT_ROOT']  ] 
			; 
			break ; 

	}

	try
	{
		$o_bdd = new PDO( "mysql:host={$G_DB['host']};dbname={$G_DB['dbname']};charset=utf8", $G_DB['user'] , $G_DB['password'] );
	}
	catch (Exception $e)
	{
		var_dump($G_DB);
	    die(' An error occured while trying to connect : ' . $e->getMessage());
	} 

	// define vars
	define( 'ROOT' , $G_DB['root'] ) ;
	define( 'CACHE_MODE' , false ) ; 
	define( 'CACHE_PATH' , '/cache/' ) ; 
	


	
