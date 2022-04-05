<?php
	

	$host = $_SERVER['HTTP_HOST'] ; 


	switch ( $host )
	{

		default : 
			$G_DB = [ 'host' => 'localhost' , 'dbname' => 'gco' , 'user' => 'admin' , 'password' => 'admin' , 'host_api' => 'http://www.gco.local/api/' , 'root' => $_SERVER['DOCUMENT_ROOT']  ] ; 
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
	


	
