<?php
	
	class CanDataFormat
	{

		/**
		* Output different format
		*
		*/ 
		public static function output( $final_data , $mode = 'json' , $force_download = false , $minify = false )
		{
			header('Access-Control-Allow-Origin: *');
			
			$date = date_create();

			switch( $mode )
			{
				case 'csv' : 
					$file_csv = $_SERVER['DOCUMENT_ROOT'] . '/tmp/file'.date_timestamp_get($date).'.csv' ; 
					$fp = fopen( $file_csv , 'w');
					foreach ( $final_data['data'] as $fields ) @fputcsv( $fp, $fields) ;
					fclose($fp);
					header('Content-Type: application/csv');
					header('Content-Disposition: attachment; filename=data.csv');
					readfile( $file_csv );
					// remove the file 
					break ; 

				case 'xml' : 
					$xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
					array_to_xml( $final_data['data'] ,$xml_data);
					header('Content-Type: application/xml');
					if ( $force_download == true ) header('Content-disposition: attachment; filename=data.xml'); 
					print $xml_data->asXML();
					exit() ; 

				// json mode 
				default : 

					if ( $force_download == true ) header('Content-disposition: attachment; filename=data.json'); 
					header('Content-Type: application/json');
					echo ( $minify == true ) ? json_encode(json_encode( $final_data )) : json_encode( $final_data ) ;
					//exit() ; 
					
					break ; 
			}
		}

		public static function jsonHeader()
		{
			header('Content-Type: application/json');
		}
		
		public static function slugify( $text )
		{ 	
			// replace non letter or digits by -
		  	$text = preg_replace('~[^\\pL\d]+~u', '-', $text);

		  	// trim
		  	$text = trim($text, '-');

		  	// transliterate
		  	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		  	// lowercase
		  	$text = strtolower($text);

		  	// remove unwanted characters
		  	$text = preg_replace('~[^-\w]+~', '', $text);

		  	if (empty($text))
		  	{
		    	return 'n-a';
		  	}
		  	return $text;
		}

	} // end class