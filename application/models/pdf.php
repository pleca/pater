<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(SYS_DIR . '/libraries/tcpdf/tcpdf.php');

class Pdf {

	private $pdf;

	public function __construct() {
		$pdf = new TCPDF();
	}

	public function __destruct() {
		
	}

	function generatePdf($order, $status, $doc, $template, $location = 1, $path = '') {  // 1 - otworz w przegladarce, 2 - zapisz na dysku
		global $l;

		$name = $doc['id'] . ' ' . $order['id'];
		if ($location == 2)
			$file = $path . makeUrl($name) . '.pdf';
		else
			$file = makeUrl($name) . '.pdf';
		$author = $_SESSION[USER_CODE]['name'] . ' ' . $_SESSION[USER_CODE]['surname'];

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8');

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($author);
		$pdf->SetTitle($name);
		$pdf->SetSubject($name);
		$pdf->SetKeywords('Vital Max,');
		if ($order['lang_id'] == 2)
			$pdf->setFont('arial', '', 10, '', false);
		else
			$pdf->setFont('', '', 10, '', false);
		$pdf->addPage();
		$y = $pdf->getY();

		$html = '<img width="100" src="' . CMS_URL . '/files/graphics/logo.png" alt="' .Cms::$conf['company_name'] .'" />';
		$pdf->writeHTMLCell(190, 50, 10, $y, $html, 0);

		$pdf->Ln();
		$y = $pdf->getY();
		$html = $doc['client'];
		$pdf->writeHTMLCell(20, '', 10, $y, $html, 0, 1, 0, true, 'L');
        
		$html = $order['shipping_first_name'] . ' ' . $order['shipping_last_name'] . '<br />';
		$html.= $order['shipping_address1'] . '<br />';
		if ($order['shipping_address2'])
			$html.= $order['shipping_address2'] . '<br />';
		if ($order['shipping_address3'])
			$html.= $order['shipping_address3'] . '<br />';
		$html.= $order['shipping_city'] . '<br />';
		$html.= $order['shipping_post_code'] . '<br />';
        
		if ($order['shipping_country_name'])
			$html.= $order['shipping_country_name'] . '<br />';
		if ($order['email'])
			$html.= $order['email'] . '<br />';
		if ($order['phone'])
			$html.= $order['shipping_phone'];
		$pdf->writeHTMLCell(80, '', 30, $y, $html, 0, 1, 0, true, 'L');
		$html = $doc['id'] . '<br />';
		$html.= $doc['date'] . '<br />';
		if ($order['payment'])
			$html.= $doc['payment'] . '<br />';
		$html.= $doc['transport'];
		$pdf->writeHTMLCell(32, '', 110, $y, $html, 0, 1, 0, true, 'L');
		$html = $order['id'] . '<br />';
                
		$html.= $order['time_add'] . '<br />';
		if ($order['payment'])
			$html.= $order['payment'] . '<br />';
		$html.= $order['transport_name'];
		$pdf->writeHTMLCell(58, '', 142, $y, $html, 0, 1, 0, true, 'L');

		// tabela z produktami
		$pdf->Ln();
		$pdf->Ln(20);
		$pdf->setFont('', '', 8);
		$pdf->SetLineWidth(0.3);

		$pdf->MultiCell(6, 8, $doc['lp'], 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
		$pdf->MultiCell(97, 8, $doc['name'], 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
		$pdf->MultiCell(13, 8, $doc['amount'], 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
		$pdf->MultiCell(20, 8, $doc['net'] . ' [' . $doc['currency'] . ']', 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
		$pdf->MultiCell(14, 8, $doc['vat'] . ' [%]', 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
		$pdf->MultiCell(20, 8, $doc['gross'] . ' [' . $doc['currency'] . ']', 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
		$pdf->MultiCell(20, 8, $doc['sum'] . ' [' . $doc['currency'] . ']', 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');

		$pdf->SetLineWidth(0.2);
		foreach ($order['products'] as $k => $v) {
			if (strlen($v['name']) > 35) {
				$h = 8;
				$m = 'T';
			} else {
				$h = 5;
				$m = 'M';
			}
			$pdf->Ln();
            
            if ($k > 0 && ($k % 12 == 0)) {            
                $y = 269;
                $pdf->setFont('', '', 8);
                $html = CMS::$conf['pdf_footer'];
                $pdf->writeHTMLCell(190, '', 10, $y, $html, 1, 1, 0, true, 'C'); 
                
                $pdf->addTOCPage();
                $pdf->SetLineWidth(0.3);
                $pdf->MultiCell(6, 8, $doc['lp'], 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
                $pdf->MultiCell(97, 8, $doc['name'], 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
                $pdf->MultiCell(13, 8, $doc['amount'], 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
                $pdf->MultiCell(20, 8, $doc['net'] . ' [' . $doc['currency'] . ']', 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
                $pdf->MultiCell(14, 8, $doc['vat'] . ' [%]', 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
                $pdf->MultiCell(20, 8, $doc['gross'] . ' [' . $doc['currency'] . ']', 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');
                $pdf->MultiCell(20, 8, $doc['sum'] . ' [' . $doc['currency'] . ']', 1, 'C', 0, 0, '', '', true, 0, false, true, 8, 'T');      
                $pdf->Ln();
                $pdf->SetLineWidth(0.2);                               
            }
            
			$pdf->MultiCell(6, $h, $k + 1, 1, 'C', 0, 0, '', '', true, 0, false, true, $h, $m);
			$pdf->MultiCell(97, $h, $v['name'] . ' <small> ' .$v['desc'] . '</small>', 1, 'L', 0, 0, '', '', true, 0, true, true, $h, $m);
			$pdf->MultiCell(13, $h, $v['qty'], 1, 'C', 0, 0, '', '', true, 0, false, true, $h, $m);
			$pdf->MultiCell(20, $h, $v['price'], 1, 'R', 0, 0, '', '', true, 0, false, true, $h, $m);
			$pdf->MultiCell(14, $h, $v['tax_val'], 1, 'R', 0, 0, '', '', true, 0, false, true, $h, $m);
			$pdf->MultiCell(20, $h, $v['price_gross'], 1, 'R', 0, 0, '', '', true, 0, false, true, $h, $m);
			$pdf->MultiCell(20, $h, $v['sum'], 1, 'R', 0, 0, '', '', true, 0, false, true, $h, $m);
		}

		$pdf->Ln();
		$pdf->MultiCell(136, 5, '', 0, 'L', 0, 0, '', '', true, 0, false, true, 5, 'T');
		$pdf->MultiCell(34, 5, $doc['net2'], 1, 'L', 0, 0, '', '', true, 0, false, true, 5, 'M');
		$pdf->MultiCell(20, 5, $order['price'], 1, 'R', 0, 0, '', '', true, 0, false, true, 5, 'M');
		$pdf->Ln();
		$pdf->MultiCell(136, 5, '', 0, 'L', 0, 0, '', '', true, 0, false, true, 5, 'T');
		$pdf->MultiCell(34, 5, $doc['vat2'], 1, 'L', 0, 0, '', '', true, 0, false, true, 5, 'M');
		$pdf->MultiCell(20, 5, $order['tax_val'], 1, 'R', 0, 0, '', '', true, 0, false, true, 5, 'M');
		$pdf->Ln();
		$pdf->MultiCell(136, 5, '', 0, 'L', 0, 0, '', '', true, 0, false, true, 5, 'T');
		$pdf->MultiCell(34, 5, $doc['gross2'], 1, 'L', 0, 0, '', '', true, 0, false, true, 5, 'M');
		$pdf->MultiCell(20, 5, $order['price_gross'], 1, 'R', 0, 0, '', '', true, 0, false, true, 5, 'M');
		if ($order['discount'] > 0) {
			$pdf->Ln();
			$pdf->MultiCell(136, 5, '', 0, 'L', 0, 0, '', '', true, 0, false, true, 5, 'T');
			$pdf->MultiCell(34, 5, $doc['discount'], 1, 'L', 0, 0, '', '', true, 0, false, true, 5, 'M');
			$pdf->MultiCell(20, 5, $order['discount'] . '%', 1, 'R', 0, 0, '', '', true, 0, false, true, 5, 'M');
			$pdf->Ln();
			$pdf->MultiCell(136, 5, '', 0, 'L', 0, 0, '', '', true, 0, false, true, 5, 'T');
			$pdf->MultiCell(34, 5, $doc['saving'], 1, 'L', 0, 0, '', '', true, 0, false, true, 5, 'M');
			$pdf->MultiCell(20, 5, $order['saving'], 1, 'R', 0, 0, '', '', true, 0, false, true, 5, 'M');
		}
		$pdf->Ln();
		$pdf->MultiCell(136, 5, '', 0, 'L', 0, 0, '', '', true, 0, false, true, 5, 'T');
		$pdf->MultiCell(34, 5, $doc['transport2'], 1, 'L', 0, 0, '', '', true, 0, false, true, 5, 'M');
		$pdf->MultiCell(20, 5, $order['transport_price'], 1, 'R', 0, 0, '', '', true, 0, false, true, 5, 'M');
		$pdf->Ln();
		$pdf->MultiCell(136, 5, '', 0, 'L', 0, 0, '', '', true, 0, false, true, 5, 'T');
		$pdf->MultiCell(34, 5, $doc['total'], 1, 'L', 0, 0, '', '', true, 0, false, true, 5, 'M');
		$pdf->MultiCell(20, 5, $order['total'], 1, 'R', 0, 0, '', '', true, 0, false, true, 5, 'M');

		$pdf->Ln();
		$pdf->Ln(20);
		$y = $pdf->getY();
		if ($order['comment']) {
			$html = $doc['comments'] . ': ' . $order['comment'];
			$pdf->writeHTMLCell(190, '', 10, $y, $html, 0);
		}

		if ($template['desc']) {   // reklama
			$pdf->Ln();
			$y = 210;
			$pdf->setFont('', '', 10);
			$html = $template['desc'];
			$pdf->writeHTMLCell(190, '', 10, $y, $html, 1, 1, 0, true, 'L');
		}

		$pdf->Ln();
		$y = 269;
		$pdf->setFont('', '', 8);
		$html = CMS::$conf['pdf_footer'];
		$pdf->writeHTMLCell(190, '', 10, $y, $html, 1, 1, 0, true, 'C');

		ob_end_clean();
		if ($location == 2) {
			$pdf->Output($file, 'F');
			return $file;
		} else {
			$pdf->Output($file, 'D');
		}

	}

}
