<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');

class LogisticService extends BaseModel {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'logistic_services';
	}
	
	public function getAll($setIdAsKey = false) {
        if ($setIdAsKey) {
            $entities = $this->select($this->table);
            $list = [];
            foreach ($entities as $entity) {
                $list[$entity['id']] = $entity;
            }
            
            return $list;
        }
		return $this->select($this->table, '1', 'id','desc');
	}
	
	public function get_last_id() {
		$q = "SELECT MAX(`id`) FROM `".$this->table."` ";
		
		if ($item = Cms::$db->max($q)) {
			return (int)$item[0];
		}
		
		return false;
	}
	
	public function get_table_fields() {
		$q = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND TABLE_NAME='".$this->table."' ";
		$array =  Cms::$db->getAll($q);
		$items = array();
		
		foreach($array as $v) {
			$items[] = $v['COLUMN_NAME'];
		}
		
		return $items;		
	}
	
	public function generate_sscc($last_id) {
		$new_id = $last_id + 1;
			// Seryjny Numer Jednostki Wysyłkowej (SSCC)
			// Cyfra uzupełniajca Prefiks firmy GS1 i oznaczenie jednostki Cyfra kontrolna
			// N1 N2 N3 N4 N5 N6 N7 N8 N9 N10 N11 N12 N13 N14 N15 N16 N17 N18
		$n1 = 0;
		$n2 = 590;	// kraj
		$n3 = 825269;	// frima
		$n4 = str_pad($new_id, 7, 0, STR_PAD_LEFT);	// nr paczki | 7 znakow
		$n5 = 'X';	// cyfra kontrolna

			// wyliczanie cyfry kontrolnej | SK = 10 - (w1(c1+c3+c5+...+cn) + w2(c2+c4+c6+...+cp)) mod 10		
		$sscc = $n1.$n2.$n3.$n4;
		$w1 = 3;
		$w2 = 1;
		$t = str_split($sscc);
		$n5 = 10 - ($w1 * ($t[0]+$t[2]+$t[4]+$t[6]+$t[8]+$t[10]+$t[12]+$t[14]+$t[16]) + $w2 * ($t[1]+$t[3]+$t[5]+$t[7]+$t[9]+$t[11]+$t[13]+$t[15])) % 10; 

		$sscc = $sscc.$n5;	// dokldamy cyfre kontrolna
		return $sscc;
	}

	public function add($post) {
		$post = maddslashes($post);

		$last_id = $this->get_last_id();
		$new_id = $last_id + 1;
		$sscc = $this->generate_sscc($last_id);

		$q = "INSERT INTO ".$this -> table." SET `id`='".$new_id."', `company_name`='".$post['company_name']."', `company_address_1`='".$post['company_address_1']."', ";
		$q.= "`company_address_2`='".$post['company_address_2']."', `company_address_3`='".$post['company_address_3']."', `customer_name`='".$post['customer_name']."', ";
		$q.= "`customer_address_1`='".$post['customer_address_1']."', `customer_address_2`='".$post['customer_address_2']."', `customer_address_3`='".$post['customer_address_3']."', ";
		$q.= "`product_name`='".$post['product_name']."', `gtin`='".$post['gtin']."', `order_number`='".$post['order_number']."', `best_before`='".$post['best_before']."', ";
		$q.= "`weight`='".$post['weight']."', `count_item`='".$post['count_item']."', `count_euro`='".$post['count_euro']."', `sscc`='".$sscc."', ";
		$q.= "`palette_height`='".$post['palette_height']."', `login`='".$_SESSION[USER_CODE]['login']."', `date_add`=NOW(), `date_mod`=NOW() ";
		$id = Cms::$db->insert($q);
   //				$this -> db -> insert($q);

		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_add']);
		return $id;
	}

	public function edit($post) {
		$post = maddslashes($post);

		$q = "UPDATE ".$this -> table." SET `company_name`='".$post['company_name']."', `company_address_1`='".$post['company_address_1']."', ";
		$q.= "`company_address_2`='".$post['company_address_2']."', `company_address_2`='".$post['company_address_2']."', `customer_name`='".$post['customer_name']."', ";
		$q.= "`customer_address_1`='".$post['customer_address_1']."', `customer_address_2`='".$post['customer_address_2']."', `customer_address_3`='".$post['customer_address_3']."', ";
		$q.= "`product_name`='".$post['product_name']."', `gtin`='".$post['gtin']."', `order_number`='".$post['order_number']."', `best_before`='".$post['best_before']."', ";
		$q.= "`weight`='".$post['weight']."', `count_item`='".$post['count_item']."', `count_euro`='".$post['count_euro']."', ";
		$q.= "`palette_height`='".$post['palette_height']."', `login`='".$_SESSION[USER_CODE]['login']."', `date_mod`=NOW() ";

		$q.= "WHERE `id`='".(int)$post['id']."'";
		Cms::$db->update($q);		

		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);

		return true;
	}

	function deleteById($id = 0) {
		if ($id > 0) {
			$q = "DELETE FROM `".$this -> table."` WHERE `id`='".(int)$id."' ";
			if (Cms::$db->delete($q)) {

				Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);			   
				return true;
			}
		}

		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
		return false;
	}
	
	public function generate($post = '') {				
		require_once(SYS_DIR . '/libraries/tcpdf/tcpdf.php');
		
		$date = date('d-m-Y_H-i-s');
		$DS = DIRECTORY_SEPARATOR;

		$this->_pdf = new TCPDF('L', 'mm', array(297, 210), true, 'UTF-8');
		$pdf = &$this->_pdf;
        $pdf->SetTitle("Sweetworld");
        $pdf->SetAuthor("Sweetworld");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(1, 1);
        $pdf->SetAutoPageBreak(true, 0);
		
		$pdf->AddPage('L', array(297, 210));
		$pdf->SetDisplayMode('real');
		
		$this->index_post_layout($post, 0);
		$this->index_post_layout($post, 148);
		
		//$pdf->WriteHTML($html, true, false, true, false, '');
		$pdf->lastPage();
		ob_end_clean();
        $pdf->Output('etykieta-logistyczna_' . $date . '.pdf', 'D');		
	}

	private function index_post_layout($post = '', $x = 0) {
		
		$pdf = &$this->_pdf;
		
		$lineStyle = array('width' => 0.5, 'color' => array(0, 0, 0));
		
		$style = array(
			'position' => '',
			'align' => 'C',
			'stretch' => false,
			'fitwidth' => false,
			'cellfitalign' => '',
			'border' => false,
			'hpadding' => 'auto',
			'vpadding' => 'auto',
			'fgcolor' => array(0,0,0),
			'bgcolor' => false, //array(255,255,255),
			'text' => true,
			'font' => 'helvetica',
			'fontsize' => 9,
			'stretchtext' => 4
		);
		
		// Dostawca
		$pdf->setFont('arial', '', 8, '', false);
		$pdf->SetTextColor(120, 120, 120);
		
		$sTop	= 'Dostawca';
		
		$pdf->MultiCell(74, 10, $sTop, 0, 'C', false, 1, 0 + $x, 1, true, 0, false, true, 0, 'T', false);
		
		$pdf->setFont('arial', 'B', 8, '', false);
		$pdf->SetTextColor(0, 0, 0);
		
		$sTop	= isset($post['company_name']) ? $post['company_name'] : '';
		
		$pdf->MultiCell(74, 10, $sTop, 0, 'C', false, 1, 0 + $x, 6, true, 0, false, true, 0, 'T', false);
		
		$pdf->setFont('arial', '', 8, '', false);
		
		$sTop = isset($post['company_address_1']) ? $post['company_address_1'] : '';
		$sTop .= isset($post['company_address_2']) ? "\n" . $post['company_address_2'] : '';
		$sTop .= isset($post['company_address_3']) ? "\n" . $post['company_address_3'] : '';
		
		$pdf->MultiCell(74, 10, $sTop, 0, 'C', false, 1, 0 + $x, 10, true, 0, false, true, 0, 'T', false);
		// \ Dostawca
		
		// Odbiorca
		$pdf->setFont('arial', '', 8, '', false);
		$pdf->SetTextColor(120, 120, 120);
		$sTop	= 'Odbiorca';
		
		$pdf->MultiCell(74, 10, $sTop, 0, 'C', false, 1, 74 + $x, 1, true, 0, false, true, 0, 'T', false);
		
		$pdf->setFont('arial', 'B', 8, '', false);
		$pdf->SetTextColor(0, 0, 0);
		
		$sTop	= isset($post['customer_name']) ? $post['customer_name'] : '';
		
		$pdf->MultiCell(74, 10, $sTop, 0, 'C', false, 1, 74 + $x, 6, true, 0, false, true, 0, 'T', false);
		
		$pdf->setFont('arial', '', 8, '', false);
		
		$sTop = isset($post['customer_address_1']) ? $post['customer_address_1'] : '';
		$sTop .= isset($post['customer_address_2']) ? "\n" . $post['customer_address_2'] : '';
		$sTop .= isset($post['customer_address_3']) ? "\n" . $post['customer_address_3'] : '';
		
		$pdf->MultiCell(74, 10, $sTop, 0, 'C', false, 1, 74 + $x, 10, true, 0, false, true, 0, 'T', false);
		// \ Odbiorca
		
		// Nazwa produktu
		$pdf->setFont('arial', 'B', 18, '', false);
		
		$sTop = isset($post['product_name']) ? $post['product_name'] : '';
		
		$pdf->MultiCell(148, 10, $sTop, 0, 'C', false, 1, 0 + $x, 30, true, 0, false, true, 0, 'T', false);
		// \ Nazwa produktu
		
		$pdf->Line(0 + $x, 52, 148 + $x, 52, $lineStyle);
		
		
			// Srodek
		$sCenter = "ZAWARTOŚĆ:\n";
		$pdf->setFont('arial', '', 10);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 5 + $x, 55, true, 0, false, true, 0, 'T', false);
		
		$sCenter = isset($post['gtin']) ? $post['gtin'] : '';		
		$pdf->setFont('arial', 'B', 11);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 5 + $x, 60, true, 0, false, true, 0, 'T', false);
		
		
		$sCenter = "LICZBA PALET:\n";
		$pdf->setFont('arial', '', 10);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 5 + $x, 67, true, 0, false, true, 0, 'T', false);
		
		$sCenter = isset($post['count_euro']) ? $post['count_euro'] : '';		
		$pdf->setFont('arial', 'B', 11);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 5 + $x, 72, true, 0, false, true, 0, 'T', false);
		
		
		$sCenter = "NR ZAMOWIENIA:\n";
		$pdf->setFont('arial', '', 10);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 5 + $x, 79, true, 0, false, true, 0, 'T', false);
		
		$sCenter = isset($post['order_number']) ? $post['order_number'] : '';	
		$pdf->setFont('arial', 'B', 11);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 5 + $x, 84, true, 0, false, true, 0, 'T', false);
		
		
		$sCenter = "SSCC:\n";
		$pdf->setFont('arial', '', 10);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 5 + $x, 91, true, 0, false, true, 0, 'T', false);
		
		$sCenter = isset($post['sscc']) ? $post['sscc'] : '';
		$pdf->setFont('arial', 'B', 11);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 5 + $x, 96, true, 0, false, true, 0, 'T', false);
		
		
		$sCenter = "NAJLEPSZE DO:\n";
		$pdf->setFont('arial', '', 10);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 74 + $x, 55, true, 0, false, true, 0, 'T', false);
		
		$sCenter = isset($post['best_before']) ? $post['best_before'] : '';
		$pdf->setFont('arial', 'B', 11);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 74 + $x, 60, true, 0, false, true, 0, 'T', false);
						
		
		$sCenter = "LICZBA SZTUK:\n";
		$pdf->setFont('arial', '', 10);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 74 + $x, 67, true, 0, false, true, 0, 'T', false);
		
		$sCenter = isset($post['count_item']) ? $post['count_item'] : '';		
		$pdf->setFont('arial', 'B', 11);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 74 + $x, 72, true, 0, false, true, 0, 'T', false);
		
		
		$sCenter = "WAGA:\n";
		$pdf->setFont('arial', '', 10);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 74 + $x, 79, true, 0, false, true, 0, 'T', false);
		
		$sCenter = isset($post['weight']) ? $post['weight'] : '';
		$pdf->setFont('arial', 'B', 11);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 74 + $x, 84, true, 0, false, true, 0, 'T', false);		
		

		$sCenter = "WYSOKOŚĆ PALETY:\n";
		$pdf->setFont('arial', '', 10);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 74 + $x, 91, true, 0, false, true, 0, 'T', false);
		
		$sCenter = isset($post['palette_height']) ? $post['palette_height'] : '';
		$pdf->setFont('arial', 'B', 11);
		$pdf->MultiCell(70, 5, $sCenter, 0, 'L', false, 1, 74 + $x, 96, true, 0, false, true, 0, 'T', false);		
		// \ Srodek
		
		
		$pdf->setFont('arial', '', 10);
		$pdf->Line(0 + $x, 112, 148 + $x, 112, $lineStyle);
		
		// Kody kreskowe
		$sBarcode1 = "(02)";
		$sBarcode1 .= isset($post['gtin']) ? $post['gtin'] : '';
		$sBarcode1 .= "(37)";
		$sBarcode1 .= isset($post['count_item']) ? $post['count_item'] : '';
		
		$pdf->write1DBarcode($sBarcode1, 'C128', 0 + $x, 115, 145, 30, 0.4, $style, 'N');
		
		$sBarcode2 = "(15)";
		$sBarcode2 .= isset($post['best_before']) ? str_replace(array('.','-',':',' '),'',$post['best_before']) : '';
		$sBarcode2 .= "(10)";
		$sBarcode2 .= isset($post['batch_number']) ? str_replace(array('.','-',':',' '),'',$post['batch_number']) : '';
		
		$pdf->write1DBarcode($sBarcode2, 'C128', 0 + $x, 145, 145, 30, 0.4, $style, 'N');
		
		$sBarcode3 = "(00)";
		$sBarcode3 .= isset($post['sscc']) ? $post['sscc'] : '';
		
		$pdf->write1DBarcode($sBarcode3, 'C128', 0 + $x, 175, 145, 30, 0.4, $style, 'N');
		// \ Kody kreskowe
		
	}	

//	public function set($item = '') {		
//		if(!$item) {
//			return false;
//		}
//		return $this->insert($this->table, $item);
//	}

   
}
