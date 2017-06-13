<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(LIB_DIR . '/class.upload.php');

class UploadAdmin extends upload {

	public function __construct() {
		
	}
	
	public function __destruct() {
		
	}

	// plik z POST, nazwa, katalog, kadrowanie, medium, small, extra
	public function add_image($file, $name, $dir, $ratio = '', $x1 = 0, $y1 = 0, $x2 = 0, $y2 = 0, $x3 = 0, $y3 = 0) {
		if (!empty($file['name'])) {
			// dodawanie zdjecia, robienie miniaturek
			$handle = new Upload($file); // inicjalizacja class, dodawanie foto z formularza, opcjonalnie lang
			if (isset($file['local']))
				$handle->no_upload_check = 1;   // jesli zmianna locla isteniej, to plik nie pochodzi z formularza
			if ($handle->uploaded) {
				$handle->jpeg_quality = 100;
				$max = 1200;
				if ($file['size'] < 1) {
					$handle->no_upload_check = 1;
				}
				$fileName = $name . '.' . $handle->file_src_name_ext;
				$handle->file_new_name_body = $name;
				if ($handle->image_src_x > $max || $handle->image_src_y > $max) {
					$handle->image_resize = true;
					$handle->image_ratio = true;
					$handle->image_x = $max;
					$handle->image_y = $max;
				}
				$handle->Process($dir);

				if ($x1 > 0 && $y1 > 0) {
					$handle->file_new_name_body = $name;
					$handle->file_name_body_add = '_m';
					$handle->image_resize = true;
					if ($ratio == 'y') {
						$handle->image_ratio_y = true;
						$handle->image_x = $x1;
					} elseif ($ratio == 'x') {
						$handle->image_ratio_x = true;
						$handle->image_y = $y1;
					} elseif ($ratio == 'c') {
						$handle->image_ratio_crop = true;
						$handle->image_x = $x1;
						$handle->image_y = $y1;
					} elseif ($ratio == 'k') {
						$handle->image_resize = true;
						$handle->image_ratio = true;
						$handle->image_x = $x1;
						$handle->image_y = $y1;
					}
					$handle->Process($dir);
				}

				if ($x2 > 0 && $y2 > 0) {
					$handle->file_new_name_body = $name;
					$handle->file_name_body_add = '_s';
					$handle->image_resize = true;
					if ($ratio == 'y') {
						$handle->image_ratio_y = true;
						$handle->image_x = $x2;
					} elseif ($ratio == 'x') {
						$handle->image_ratio_x = true;
						$handle->image_y = $y2;
					} elseif ($ratio == 'c') {
						$handle->image_ratio_crop = true;
						$handle->image_x = $x2;
						$handle->image_y = $y2;
					} elseif ($ratio == 'k') {
						$handle->image_resize = true;
						$handle->image_ratio = true;
						$handle->image_x = $x2;
						$handle->image_y = $y2;
					}
					$handle->Process($dir);
				}

				if ($x3 > 0 && $y3 > 0) {
					$handle->file_new_name_body = $name;
					$handle->file_name_body_add = '_e';
					$handle->image_resize = true;
					if ($ratio == 'y') {
						$handle->image_ratio_y = true;
						$handle->image_x = $x3;
					} elseif ($ratio == 'x') {
						$handle->image_ratio_x = true;
						$handle->image_y = $y3;
					} elseif ($ratio == 'c') {
						$handle->image_ratio_crop = true;
						$handle->image_x = $x3;
						$handle->image_y = $y3;
					} elseif ($ratio == 'k') {
						$handle->image_resize = true;
						$handle->image_ratio = true;
						$handle->image_x = $x3;
						$handle->image_y = $y3;
					}
					$handle->Process($dir);
				}

				// we check if everything went OK
				if (!$handle->processed) {
					// one error occured
//                    dump($handle->error, 'ERROR');
                    Cms::getFlashBag()->add('error', $handle->error);
					
					return false;
				}
				$handle->Clean();
				return $fileName;
			} else {
				// if we're here, the upload file failed for some reasons
				// i.e. the server didn't receive the file
                Cms::getFlashBag()->add('error', $handle->error);
				return false;
			}
		}
	}

}
