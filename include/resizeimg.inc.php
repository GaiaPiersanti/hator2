<?php
/* questa funzione arrangia l'immagine in modo che prenda quelle dimensioni date
   possiamo modificarlo in modo che prend apiù tipi di file
   per poterlo utilizzare dobiamo richiamarlo in un file php*/ 
function get_thumbnail($file,$width = 300,$height = 300){
 
		if(file_exists($file)){
		
			$file_extention = explode(".",$file);
			$file_extention = end($file_extention);
			
			if(strtolower($file_extention) == "jpg" || strtolower($file_extention) == "jpeg"){
			
				$thumb = str_replace("." . $file_extention,"_thumb." . $file_extention,$file);
		 
		  
				if(file_exists($thumb)){
		  
					return $thumb;
				 
				}else{
				
					make_new:
					//create a square thumbnail for display
				 
						//$this->resize_image_crop($file,$thumb,$width,$height);
				 
					
					if(file_exists($thumb)){
					
						return $thumb;
					
					}else{
					
						return($file);
						
					}
					
				}
				
			}else{
			
				return $file;
			
			}

		}else{
		
			return $file;
		
		}

	}
?>