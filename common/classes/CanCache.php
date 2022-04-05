<?php
	
	class CanCache
	{

		public static function getFile( $folder , $ext )
		{
			if ( !empty($_SERVER['REQUEST_URI'])) 
				return $file_name = CACHE_PATH . "$folder/" . CanDataFormat::slugify($_SERVER['REQUEST_URI']).".$ext" ; 
		}

		public static function start( $file_name )
		{
	      	if ( CACHE_MODE == true )
	      	{	

	        	// check if html cache is present 
	        	if ( file_exists( ROOT . $file_name ))
	        	{
	          		$content = file_get_contents( ROOT . $file_name ) ; 
	          		exit( $content ) ; 
        		}
        		else
		        {
		          	ob_start(); 
		        }
        	}
	        

		} // end function  

		public static function end( $file_name )
		{
			if ( CACHE_MODE == true )
		    {
		        $content = ob_get_contents();
		        ob_end_clean();
		        fopen( ROOT . $file_name , "w+" ) ; 
		        file_put_contents( ROOT . $file_name, $content ) ;
		        echo $content ; 
		    }
		}

	} // end cache