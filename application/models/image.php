<?php
/* 2011-01-03 | creative.cms */

if(!defined('NO_ACCESS')) die('No access to files!');
 
class Image
{
	var $filename;
	var $newFilename;
	var $newW;
	var $newH;	
	var $_content;
	var $_newContent;
	var $_type;	
	var $_w;
	var $_h;
	var $_error;

	function __construct() {}
	
	/* funkcja usuwa zmienne, na ktorych operuje */
	function clearVars()
	{
		$this -> imgDestroy();
		unset($this -> filename);
		unset($this -> newFilename);
		unset($this -> newW);
		unset($this -> newH);
		unset($this -> _content);
		unset($this -> _newContent);
		unset($this -> _type);
		unset($this -> _w);	
		unset($this -> _h);	
		unset($this -> _error);
		return true;
	}
	
	/* funkcja dodaje obrazek na ktorym klasa bedzie operowac */
	function addImage($filename='', $append='_s')
	{
		$this -> clearVars();
		$lastDot = strrpos($filename, '.');
		$ext = substr($filename, $lastDot, strlen($filename));
		$filename = str_replace($ext, '', $filename);
		
		if(empty($filename))
		{
			$this -> _error = 1;
			return false;
		}else{
			$this -> filename = $filename.$ext;
			$this -> newFilename = $filename.$append.$ext;
			return true;
		}
	}

	/* funkcja odczytuje informacje o obrazku */
	function readImageInfo()
	{
		if($imageInfo = getimagesize($this -> filename))
		{
			$this -> _w = $imageInfo[0];
			$this -> _h = $imageInfo[1];
			$this -> _type = $imageInfo[2];
			$ratio = $this -> _h / $this -> _w;
			$this -> newH = (int)round($this -> newW * $ratio);
			
			$this -> _newContent = ImageCreateTrueColor($this -> newW, $this -> newH);
			if($this -> _type == 3)
			{
				imagefilledrectangle($this -> _newContent, 0, 0, $this -> newW, $this -> newH, imagecolorallocatealpha($this -> _newContent, 255, 255, 255, 0));
			}

			if(function_exists('imageantialias')) ImageAntiAlias($this -> _newContent, true);
			unset($imageInfo);
			return true;
		}else{
			$this -> _error = 2;
			return false;
		}
	}

	/* funkcja wczytuje obrazek do pamieci */	
	function readImageContent()
	{
		switch($this -> _type)
		{
			case '1' :	// GIF 
				$img = ImageCreateFromGif($this -> filename);
				break;
			case '2' : 	// JPG
				$img = imagecreatefromjpeg($this -> filename);
				break;
			case '3' : // PNG
				$img = ImageCreateFromPng($this -> filename);
				break;
			default : 
				$this -> _error = 3;
				return false;
		}
		
		if(!$img)
		{
			$this -> _error = 2;
			return false;
		}else{
			$this -> _content = $img;
			return true;			
		}
	}
	
	/* funkcja tworzy nowy obrazek o zmienionych rozmiarach na podstawie starego */
	function imageResample($max_h = 0, $operation = 0)
	{
		$width = $this -> newW;
		$height = $this -> newH;
		
		if($this -> newW > $this -> _w)
		{	
			$width = $this -> _w;
			$height = $this -> _h;
		}
		if(($max_h > 0) and ($height >= $max_h)) // gdy mamy podany rozmiar to skalujemy podwojnie... 
		{
			// najpierw skalujemy do normalnej wysokosci 
			ImageCopyResampled($this -> _newContent, $this -> _content, 0, 0, 0, 0, $width, $height, $this -> _w, $this -> _h);
			$this -> _content = $this -> _newContent;

            // 1 - kadrowanie, 2 - skalowanie, stala wysokosc, 3 - skalowanie, stala szerokosc
         if($operation == 1)
         {
               /* KADROWANIE */
            $this -> _newContent = ImageCreateTrueColor($width, $max_h);
				if($this -> _type == 3)
				{
					imagefilledrectangle($this -> _newContent, 0, 0, $width, $max_h, imagecolorallocatealpha($this -> _newContent, 255, 255, 255, 0));
				}
				
				
            if(function_exists('imageantialias')) ImageAntiAlias($this -> _newContent, true);
         }
         elseif($operation == 2)
         {
               /* SKALOWANIE DO WYSOKOSCI */
            $ratio = $width / $height;
            $new_width = (int)round($max_h * $ratio);
            $new_height = $max_h;

            $this -> _newContent = ImageCreateTrueColor($new_width, $new_height);
				if($this -> _type == 3)
				{
					imagefilledrectangle($this -> _newContent, 0, 0, $new_width, $new_height, imagecolorallocatealpha($this -> _newContent, 255, 255, 255, 0));
				}
				
            if(function_exists('imageantialias')) ImageAntiAlias($this -> _newContent, true);

            // a teraz przeskalowujemy do odpowiedniej wysokosci
            return ImageCopyResampled($this -> _newContent, $this -> _content, 0, 0, 0, 0, $new_width,$new_height,$width,$height);
         }
         elseif($operation == 3)
         {
               /* SKALOWANIE DO SZEROKOSCI */
            $max_h = $width;
            $ratio = $height / $width;
            $new_height = (int)round($max_h * $ratio);
            $new_width = $max_h;

            $this -> _newContent = ImageCreateTrueColor($new_width, $new_height);
				if($this -> _type == 3)
				{
					imagefilledrectangle($this -> _newContent, 0, 0, $new_width, $new_height, imagecolorallocatealpha($this -> _newContent, 255, 255, 255, 0));
				}
				
            if(function_exists('imageantialias')) ImageAntiAlias($this -> _newContent, true);

            // a teraz przeskalowujemy do odpowiedniej szerokosci
            return ImageCopyResampled($this -> _newContent, $this -> _content, 0, 0, 0, 0, $new_width,$new_height,$width,$height);
         }
         else
         {
               /* KADROWANIE */
            $this -> _newContent = ImageCreateTrueColor($width, $max_h);
				if($this -> _type == 3)
				{
					imagefilledrectangle($this -> _newContent, 0, 0, $width, $max_h, imagecolorallocatealpha($this -> _newContent, 255, 255, 255, 0));
				}
				
            if(function_exists('imageantialias')) ImageAntiAlias($this -> _newContent, true);
         }

			// a teraz przeskalowujemy do odpowiedniej wysokosci
			return ImageCopyResampled($this -> _newContent, $this -> _content, 0, 0, 0, floor(($height-$max_h)/2), $width,$max_h,$width,$max_h); 
		}
      else
		{
         if($operation == 1)
         {
               /* KADROWANIE */
            $ratio = $width / $height;
            $height = (int)round($height * $ratio);
            $ratio2 = $this -> _w / $this -> _h;
            $this -> _w = (int)round($this -> _w / $ratio2);
            $this -> _newContent = ImageCreateTrueColor($width, $max_h);
				if($this -> _type == 3)
				{
					imagefilledrectangle($this -> _newContent, 0, 0, $width, $max_h, imagecolorallocatealpha($this -> _newContent, 255, 255, 255, 0));
				}
				
            if(function_exists('imageantialias')) ImageAntiAlias($this -> _newContent, true);
            return ImageCopyResampled($this -> _newContent, $this -> _content, 0, 0, floor(($this -> _w-$width)/2), 0, $width, $height, $this -> _w, $this -> _h);
         }

			return ImageCopyResampled($this -> _newContent, $this -> _content, 0, 0, 0, 0, $width, $height, $this -> _w, $this -> _h);
		}
	}
	
	/* funkcja tworzy miniaturke zdjecia */
	function createThumb($width = 0, $display = false, $height = 0, $operation = 0)
	{
		$this -> newW = $width;
		if($this -> readImageInfo() AND $this ->readImageContent()){
			if(($this -> newW < $this -> _w) AND ($this -> newW > 0)){
				if($this -> imageResample($height, $operation) === false){
					return false;
				}
			}else{
				$this -> _newContent = $this -> _content;
			}
			
			if($display === true)
			{
				// wyswietlamy plik
				switch($this -> _type)
				{
					case '1' : 	// GIF
						header('Content-type: image/gif');
						ImageGif($this -> _newContent);
						break;
					case '2' : // JPEG
						header('Content-type: image/jpeg');
						ImageJpeg($this -> _newContent, 90);
						break;
					case '3' : // GIF
						header('Content-type: image/png');				
						ImagePng($this -> _newContent);
					default : 
						$this -> _error = 3;
						return false;
				}			
			}else
			{
				// zapisujemy plik
				switch($this -> _type)
				{
						case '1' : 	// GIF
						ImageGif($this -> _newContent, $this -> newFilename);
						break;
					case '2' : // JPEG
							ImageJpeg($this -> _newContent, $this -> newFilename);
						break;
					case '3' : // GIF
						ImagePng($this -> _newContent, $this -> newFilename);
					default : 
						$this -> _error = 3;
						return false;
				}
			}
		}
		
		$this -> imgDestroy();
		return true;
	}
	
	function getImageType()
	{
		switch($this -> _type)
		{
			case '1' :	// GIF 
				return 'gif';
			case '2' : 	// JPG
				return 'jpg';
				break;
			case '3' : // PNG
				return 'png';
				break;
			default : 
				return false;
		}
	}

	function setNewFilename($filename)
	{
		$this -> newFilename = $filename;
		return true;
	}
	
	function imgDestroy()
	{
		if(isset($this -> _content)) 		@ImageDestroy($this -> _content);
		if(isset($this -> _newContent)) @ImageDestroy($this -> _newContent);
		return true;
	}
	
	function ErrorMsg()
	{
		switch($this -> _error)
		{
			case 1 :
				$msg = 'Nie podano nazwy pliku, na którym klasa ma operować!';
				break;
			case 2 :
				$msg = 'Brak dostępu do pliku <b>'.$this -> filename.'</b> lub plik nie istnieje!';			
				break;
			case 3 :
				$msg = 'Nieobsługiwany format pliku! Skrypt odczytuje jedynie pliki GIF, JPEG, PNG.';	
				break;
		}
	}
}
?>