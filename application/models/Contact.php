<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(CMS_DIR . '/application/models/mailer.php');
require_once(MODEL_DIR . '/EmailTemplate.php');

class Contact extends BaseModel {

	private $mailer;

	public function __construct() {
		$this->mailer = new Mailer();
		$this->table = DB_PREFIX . 'contact';
	}

	public function getById($id = '') {
		if (!$id) {
			return false;
		}
		
		$where = $this->where(["id" => $id]);
		return $this->select($this->table, $where);
	}
    
	public function getAll() {
		return $this->select($this->table);
	}    

	public function sendLink($post, $title, $url) {
		$subject = $GLOBALS['LANG']['contact_l4'] . ' ' . $_SERVER['HTTP_HOST'];
		$content = $GLOBALS['LANG']['contact_l5'] . ' <a href="' . $url . '" title="' . $title . '">' . $title . '</a><br />';
		$content.= $GLOBALS['LANG']['contact_l6'] . ' ' . $post['content'] . '<br /><br />';
		$content.= $GLOBALS['LANG']['contact_l3'] . ': ' . $post['signature'];

		$this->mailer->setSubject($subject);
		$this->mailer->setBody($content);
		if ($this->mailer->sendHTML($post['email'], EMAIL_OFFICE)) {
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['contact_l7']);
			return true;
		} else {			
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['contact_l8']);
			return false;
		}
	}

    function isValid($post) {
		if (empty($post['subject'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['contact_fill2']);
			return false;
		} elseif (empty($post['email'])) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['contact_fill2']);
			return false;
		} elseif (LOGGED != 1 AND ( !isset($_SESSION['captcha']) OR $post['captcha'] != base64_decode($_SESSION['captcha']))) {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['captcha_check']);
			return false;
		}
        
        return true;       
    }
    
    function sendEmailCustomer(array $data) {
		$emailTemplate = new EmailTemplate();
		$template = $emailTemplate->getTemplate('contact_form');			
        
		$title = str_replace($data['search_title'], $data['replace_title'], $template['title']); 
        
        //content
		$search = array('#SECTION#', '#SUBJECT#', '#BODY#', '#FIRST_NAME#', '#LAST_NAME#', '#EMAIL#', '#COMPANY_NAME#', '#PHONE#', '#DOMAIN#', '#SERVER_URL#');
		$replace = array($data['contact']['name'], $data['subject'], $data['content'], $data['first_name'], $data['last_name'], $data['email'], Cms::$conf['company_name'], $data['phone'], $_SERVER['SERVER_NAME'], $data['server_url']);
		$content = str_replace($search, $replace, $template['content']);        

		$this->mailer->setSubject($title);
		$this->mailer->setBody($content);
        
		if ($this->mailer->sendHTML($data['email'])) {
			$this->mailer->ClearAllRecipients();
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['contact_thank']);
			return true;
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['contact_error']);
			return false;
		}        
    }
    
    function sendEmailAdmin(array $data) {
        $emailTemplate = new EmailTemplate();
        $template = $emailTemplate->getTemplate('contact_form_admin');			

        $title = str_replace($data['search_title'], $data['replace_title'], $template['title']);

        //content
        $search = array('#SECTION#', '#SUBJECT#', '#BODY#', '#FIRST_NAME#', '#LAST_NAME#', '#EMAIL#', '#COMPANY_NAME#', '#PHONE#', '#DOMAIN#', '#SERVER_URL#');
        $replace = array($data['contact']['name'], $data['subject'], $data['content'], $data['first_name'], $data['last_name'], $data['email'], Cms::$conf['company_name'], $data['phone'], $_SERVER['SERVER_NAME'], $data['server_url']);
        $content = str_replace($search, $replace, $template['content']);        

        $this->mailer->setSubject($title);
        $this->mailer->setBody($content);   
            
        if ($this->mailer->sendHTML($data['contact']['email'], $data['email'])) {
            $this->mailer->ClearAllRecipients();
            return true;
        }

        return false;
    }
    
    
	function sendEmailContact($post) {
        $contact = $this->getById($post['contact_id'])[0];

        $data = array(
            'server_url'    => '<a href="'. SERVER_URL .'">' . SERVER_URL .'</a>',
            'search_title' => ['#COMPANY_NAME#', '#DOMAIN#', '#SUBJECT#', '#SECTION#'],
            'replace_title' => [Cms::$conf['company_name'], $_SERVER['SERVER_NAME'], $post['subject'], $contact['name']],
            'contact' => $contact,
        );
        
        $data = array_merge($data, $post);
        
        $this->sendEmailCustomer($data);
        $this->sendEmailAdmin($data);
	}

	function sendFormularz($post, $files) {
		$e[11] = $g = 1;
		for ($i = 1; $i <= 22; $i++) {
			if ($i == 9 OR $i == 10) {
				$e[$i] = 0;
			} elseif ($i >= 11 AND $i <= 14) {
				if (isset($post['f' . $i]))
					$e[11] = 0;
			}
			elseif ($i == 16) {
				if (isset($post['f15']) AND $post['f15'] == 1) {
					if (isset($post['f' . $i]) AND $post['f' . $i])
						$e[$i] = 0;
					else
						$e[$i] = 1;
				}
			}
			elseif ($i == 17) {
				if (isset($post['f15']) AND $post['f15'] == 2) {
					if (isset($post['f' . $i]) AND $post['f' . $i])
						$e[$i] = 0;
					else
						$e[$i] = 1;
				}
			}
			elseif ($i == 18) {
				if (isset($post['f15']) AND $post['f15'] == 3) {
					if (isset($post['f' . $i]) AND $post['f' . $i])
						$e[$i] = 0;
					else
						$e[$i] = 1;
				}
			}
			else {
				if (isset($post['f' . $i]) AND $post['f' . $i]) {
					
				} else {
					$e[$i] = 1;
				}
			}
		}
		foreach ($e as $v) {
			if ($v == 1) {
				Cms::getFlashBag()->add('error', $GLOBALS['LANG']['contact_fill2']);
				return $e;
			}
		}
		if (!preg_match("/[0-9]{9}/", $post['f4'])) {
			$e[4] = 1;
			Cms::getFlashBag()->add('error', 'VAT error, digits only.');
			return $e;
		}
		if ($post['code'] != 'MT028lesijFL67dkhjcXT082gdkpy') {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['code']);
			return $e;
		}

		$content = '
<style type="text/css">
.right{text-align:right;}
.f1{width:250px;height:20px;line-height:20px;border:1px solid #000;}
.f2{width:570px;height:50px;line-height:15px;border:1px solid #000;}
.f3{width:400px;height:20px;line-height:20px;border:1px solid #000;}
.tableEdit{text-align:left;}
.tableEdit td{padding:2px 3px;}
</style>
<table width="900" class="tableEdit" cellpadding="0" cellspacing="0" border="0">
         <tr>
            <td colspan="4" height="60">
PLEASE NOTE<br />
Applicants must complete all sections of this form. Incomplete applications cannot be processed
            </td>
         </tr>
         <tr>
            <td class="right" width="200">Contact Name</td>
            <td><input class="f1" type="text" name="f1" value="' . $post['f1'] . '" /></td>
            <td class="right">Position</td>
            <td class="right"><input class="f1" type="text" name="f2" value="' . $post['f2'] . '" /></td>
         </tr>
         <tr>
            <td class="right">Compant Trading Name</td>
            <td><input class="f1" type="text" name="f3" value="' . $post['f3'] . '" /></td>
            <td class="right">Vat No</td>
            <td class="right"><input class="f1" type="text" name="f4" value="' . $post['f4'] . '" /></td>
         </tr>
         <tr>
            <td class="right">Registered Company/<br />Invoicing Address</td>
            <td class="right" colspan="3"><textarea class="f2" name="f5">' . $post['f5'] . '</textarea></td>
         </tr>
         <tr>
            <td class="right">Delivery Address<br />(if different)</td>
            <td class="right" colspan="3"><textarea class="f2" name="f6">' . $post['f6'] . '</textarea></td>
         </tr>
         <tr>
            <td class="right">Telephone</td>
            <td><input class="f1" type="text" name="f7" value="' . $post['f7'] . '" /></td>
            <td class="right">E-mail</td>
            <td class="right"><input class="f1" type="text" name="f8" value="' . $post['f8'] . '" /></td>
         </tr>
         <tr>
            <td class="right">Fax</td>
            <td><input class="f1" type="text" name="f9" value="' . $post['f9'] . '" /></td>
            <td class="right">WWW</td>
            <td class="right"><input class="f1" type="text" name="f10" value="' . $post['f10'] . '" /></td>
         </tr>
         <tr>
            <td height="40"">Nature of your business</td>
            <td colspan="3">
               &nbsp;&nbsp;&nbsp; <input type="checkbox" id="f11" name="f11" value="1" ';
		if (isset($post['f11']))
			$content.= 'checked ';
		$content.= '/> <label for="f11">Web-based</label>
               &nbsp;&nbsp;&nbsp; <input type="checkbox" id="f12" name="f12" value="1" ';
		if (isset($post['f12']))
			$content.= 'checked ';
		$content.= '/> <label for="f12">Retail Store(s)</label>
               &nbsp;&nbsp;&nbsp; <input type="checkbox" id="f13" name="f13" value="1" ';
		if (isset($post['f13']))
			$content.= 'checked ';
		$content.= '/> <label for="f13">Health Club/Gym</label>
               &nbsp;&nbsp;&nbsp; <input type="checkbox" id="f14" name="f14" value="1" ';
		if (isset($post['f14']))
			$content.= 'checked ';
		$content.= '/> <label for="f13">Retail Store and Web</label>
            </td>
         </tr>
         <tr>
            <td colspan="4">Legal Status of your business:</td>
         </tr>
         <tr>
            <td><input type="radio" id="f15a" name="f15" value="1" ';
		if ($post['f15'] == 1)
			$content.= 'checked ';
		$content.= '/> <label for="f15a">Sole Trader</label></td>
            <td colspan="3" class="right {% if e.16 %}red{% endif %}">Provide proprietor&#39;s names <input class="f3" type="text" name="f16" value="' . $post['f16'] . '" /></td>
         </tr>
         <tr>
            <td><input type="radio" id="f15b" name="f15" value="2" ';
		if ($post['f15'] == 2)
			$content.= 'checked ';
		$content.= '/> <label for="f15b">Partnership</label></td>
            <td colspan="3" class="right {% if e.17 %}red{% endif %}">Provide all partners&#39; names <input class="f3" type="text" name="f17" value="' . $post['f17'] . '" /></td>
         </tr>
         <tr>
            <td><input type="radio" id="f15c" name="f15" value="3" ';
		if ($post['f15'] == 3)
			$content.= 'checked ';
		$content.= '/> <label for="f15c">Limited Co.</label></td>
            <td colspan="3" class="right {% if e.18 %}red{% endif %}">Provide company reg. no. <input class="f3" type="text" name="f18" value="' . $post['f18'] . '" /></td>
         </tr>
         <tr>
            <td>Estimated monthly purchases</td>
            <td colspan="3"><input class="f1" type="text" name="f19" value="' . $post['f19'] . '" /></td>
         </tr>
         <tr>
            <td valign="top">Preferred method of payment:</td>
            <td colspan="3">
               <input type="radio" id="f20a" name="f20" value="1" ';
		if ($post['f20'] == 1)
			$content.= 'checked ';
		$content.= '/> <label for="f20a">Card</label><br />
               <input type="radio" id="f20b" name="f20" value="2" ';
		if ($post['f20'] == 2)
			$content.= 'checked ';
		$content.= '/> <label for="f20b">Cheque</label><br />
               <input type="radio" id="f20c" name="f20" value="3" ';
		if ($post['f20'] == 3)
			$content.= 'checked ';
		$content.= '/> <label for="f20c">Bank transfer</label>
            </td>
         </tr>
         <tr>
            <td>Name</td>
            <td><input class="f1" type="text" name="f21" value="' . $post['f21'] . '" /></td>
            <td>Position</td>
            <td class="right"><input class="f1" type="text" name="f22" value="' . $post['f22'] . '" /></td>
         </tr>
      </table>         
';
		$subject = 'Trade Account Application';
		$email_array = explode(",", Cms::$conf['email_formularz']);
		if (is_array($email_array)) {
			$this->mailer->setSubject($subject);
			$this->mailer->setBody($content);
			foreach ($email_array as $v) {
				$this->mailer->sendHTML($v);
				$this->mailer->ClearAllRecipients();
			}
		}
	}

	function sendFormularz2($post, $files) {
		$e = array();
		for ($i = 1; $i <= 33; $i++) {
			if ($i == 1 OR $i == 2 OR ( $i >= 5 AND $i <= 9) OR $i == 15 OR $i == 21 OR $i == 29) {
				if (isset($post['f' . $i]) AND $post['f' . $i]) {
					
				} else {
					$e[$i] = 1;
				}
			}
		}
		foreach ($e as $v) {
			if ($v == 1) {
				Cms::getFlashBag()->add('error', $GLOBALS['LANG']['contact_fill2']);
				return $e;
			}
		}
		if ($post['code'] != 'MT028lesijFL67dkhjcXT082gdkpy') {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['code']);
			return $e;
		}

		$content = '
<style type="text/css">
.right{text-align:right;}
#questionnaire{width:750px;color:#006993;}
#questionnaire tr td{padding:5px 3px;}
.ff1{width:112px;height:20px;line-height:20px;border:none;border-bottom:1px solid #006993;color:#006993;}
.ff2{width:267px;height:20px;line-height:15px;border:none;border-bottom:1px solid #006993;color:#006993;}
.ff3{width:683px;height:20px;line-height:20px;border:none;border-bottom:1px solid #006993;color:#006993;}
.ff4{width:660px;height:60px;line-height:20px;border:none;border-bottom:1px solid #006993;color:#006993;}
.ff5{width:744px;height:150px;line-height:20px;border:none;border-bottom:1px solid #006993;color:#006993;}
.ff6{width:744px;height:60px;line-height:20px;border:none;border-bottom:1px solid #006993;color:#006993;}
.ff7{width:230px;height:20px;line-height:20px;border:none;border-bottom:1px solid #006993;color:#006993;}
.fff{border:1px solid #006993;background:#d9e9f2;border-radius:5px;}
.fff2{padding:10px;background:#006993;color:#fff;}
.tableEdit{text-align:left;}
.tableEdit td{padding:2px 3px;}
</style>
<table id="questionnaire" width="100%" class="tableEdit" cellpadding="0" cellspacing="0" border="0">
         <tr>
            <td height="20"></td>
         </tr>
         <tr>
            <td><span class="{% if e.1 %}red{% endif %}">Full Name: <input class="ff3" type="text" name="f1" value="' . $post['f1'] . '" /></span></td>
         </tr>
         <tr>
            <td><span class="{% if e.2 %}red{% endif %}">Home Address:<textarea class="ff4" name="f2">' . $post['f2'] . '</textarea></span></td>
         </tr>
         <tr>
            <td>
               <span class="{% if e.3 %}red{% endif %}">Home Tel: <input class="ff2" type="text" name="f3" value="' . $post['f3'] . '" /></span>
               <span class="{% if e.4 %}red{% endif %}">Work Tel (if appropriate): <input class="ff2" type="text" name="f4" value="' . $post['f4'] . '" /></span>
            </td>
         </tr>
         <tr>
            <td>
               <span class="{% if e.5 %}red{% endif %}">Mobile Tel: <input class="ff2" type="text" name="f5" value="' . $post['f5'] . '" /></span>
               <span class="{% if e.6 %}red{% edif %}">Email: <input class="ff2" type="text" name="f6" value="' . $post['f6'] . '" /></span>
            </td>
         </tr>
         <tr>
            <td>
               <span class="{% if e.7 %}red{% endif %}">Date of Birth: <input class="ff1" type="text" name="f7" value="' . $post['f7'] . '" /></span>
               <span class="{% if e.8 %}red{% endif %}">Nationality: <input class="ff1" type="text" name="f8" value="' . $post['f8'] . '" /></span>
               <span class="{% if e.9 %}red{% endif %}">Marital Status: <input class="ff1" type="text" name="f9" value="' . $post['f9'] . '" /></span>
            </td>
         </tr>
         <tr>
            <td>
               <span class="{% if e.10 %}red{% endif %}">Number of Children: <input class="ff2" type="text" name="f10" value="' . $post['f10'] . '" /></span>
               <span class="{% if e.11 %}red{% endif %}">Ages: <input class="ff2" type="text" name="f10" value="' . $post['f11'] . '" /></span>
            </td>
         </tr>
         <tr>
            <td><span class="{% if e.12 %}red{% endif %}">Health (state any serious illness or disability): <textarea class="ff6" name="f12">' . $post['f12'] . '</textarea></span></td>
         </tr>
         <tr>
            <td><span class="{% if e.13 %}red{% endif %}">Hobbies / Interests / Skills: <textarea class="ff6" name="f13">' . $post['f13'] . '</textarea></span></td>
         </tr>
         <tr>
            <td>Employment Details &nbsp;&nbsp;&nbsp; (give names of employers for the last 5 years, dates, position and any other details you consider relevant.</td>
         </tr>
         <tr>
            <td>
               <span class="{% if e.14 %}red{% endif %}">Attach a full CV if you have one): <textarea class="ff5" name="f14">' . $post['f14'] . '</textarea></span>
               <div class="right">P.T.O.</div>
            </td>
         </tr>
         <tr>
            <td>
               <span class="{% if e.15 %}red{% endif %}">Financial Information:<br />
               How much liquid (unencumbered) capital can you raise to invest in your business and how do you propose to raise this?</span>
            </td>
         </tr>
         <tr>
            <td><textarea class="ff6" name="f15">' . $post['f15'] . '</textarea></td>
         </tr>
         <tr>
            <td>
               <span class="{% if e.16 %}red{% endif %}">Do you own your own house? <input class="ff1" type="text" name="f16" value="' . $post['f16'] . '" /></span>
               <span class="{% if e.17 %}red{% endif %}">Current market value: <input class="ff1" type="text" name="f17" value="' . $post['f17'] . '" /></span>
               <span class="{% if e.18 %}red{% endif %}">Mortgage balance: <input class="ff1" type="text" name="f18" value="' . $post['f18'] . '" /></span>
            </td>
         </tr>
         <tr>
            <td>
               <span class="{% if e.19 %}red{% endif %}">Do you have a clean driving licence? <input class="ff1" type="text" name="f19" value="' . $post['f19'] . '" /></span>
               <span class="{% if e.20 %}red{% endif %}">Number of points: <input class="ff1" type="text" name="f20" value="' . $post['f20'] . '" /></span>
            </td>
         </tr>
         <tr>
            <td><span class="{% if e.21 %}red{% endif %}">Have you ever owned your own business? (give details) <textarea class="ff6" name="f21">' . $post['f21'] . '</textarea></span></td>
         </tr>
         <tr>
            <td><span class="{% if e.22 %}red{% endif %}">If yes, have you ever been involved in a business which has failed / ceased trading? (give details) <textarea class="ff6" name="f22">' . $post['f22'] . '</textarea></span></td>
         </tr>
         <tr>
            <td><span class="{% if e.23 %}red{% endif %}">Have you ever been convicted of a criminal oence other than a driving oence? (give details) <textarea class="ff6" name="f23">' . $post['f23'] . '</textarea></span></td>
         </tr>
         <tr>
            <td><span class="{% if e.24 %}red{% endif %}">Why do you wish to own your own business? <textarea class="ff6" name="f24">' . $post['f24'] . '</textarea></span></td>
         </tr>
         <tr>
            <td><span class="{% if e.25 %}red{% endif %}">What qualities do you believe are necessary to run a successful franchise? <textarea class="ff6" name="f25">' . $post['f25'] . '</textarea></span></td>
         </tr>
         <tr>
            <td><span class="{% if e.26 %}red{% endif %}">When would you wish to start your business? <input class="ff2" type="text" name="f26" value="' . $post['f26'] . '" /></span></td>
         </tr>
         <tr>
            <td><span class="{% if e.27 %}red{% endif %}">Do you plan to include anyone else in the business? (give details) <textarea class="ff6" name="f27">' . $post['f27'] . '</textarea></span></td>
         </tr>
         <tr>
            <td><span class="{% if e.28 %}red{% endif %}">Do you have the full support of your spouse and family? <input class="ff2" type="text" name="f28" value="' . $post['f28'] . '" /></span></td>
         </tr>
         <tr>
            <td>Your preferred geographical area to run an Vitamin Shop franchise would be:</td>
         </tr>
         <tr>
            <td>
               <span class="{% if e.29 %}red{% endif %}">1: <input class="ff7" type="text" name="f29" value="' . $post['f29'] . '" /></span>
               <span class="{% if e.30 %}red{% endif %}">2: <input class="ff7" type="text" name="f30" value="' . $post['f30'] . '" /></span>
               <span class="{% if e.31 %}red{% endif %}">3: <input class="ff7" type="text" name="f31" value="' . $post['f31'] . '" /></span>
            </td>
         </tr>
         <tr>
            <td class="fff">
               I understand that the above information will be kept in strictest condence by Vitamin Shop Ltd and hereby warrant that the in formation is true and accurate to the best of my
knowledge and that my signature below constitutes no obligation by Vitamin Shop Ltd or myself to enter into a franchise agreeme nt.<br /><br />
               <span class="{% if e.32 %}red{% endif %}">Signed: <input class="ff2" type="text" name="f32" value="' . $post['f32'] . '" /></span>
               <span class="{% if e.33 %}red{% endif %}">Date: <input class="ff2" type="text" name="f33" value="' . $post['f33'] . '" /></span>
            </td>
         </tr>
      </table>       
';
		$subject = 'confdential questionnaire';
		$email_array = explode(",", Cms::$conf['email_formularz2']);
		if (is_array($email_array)) {
			$file = CMS_DIR . '/files/' . $files['file']['name'];
			copy($files['file']['tmp_name'], $file);
			$this->mailer->setSubject($subject);
			$this->mailer->setBody($content);
			$this->mailer->AddAttachment($file);
			foreach ($email_array as $v) {
				$this->mailer->sendHTML($v);
				$this->mailer->ClearAllRecipients();
			}
			unlink($file);
		}
	}

	public function loadAddress() {
		$q = "SELECT * FROM `" . $this->table . "` ORDER BY `name` ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$items[] = $v;
		}
		return $items;
	}

	function add($post) {
		$post = maddslashes($post);
		$post['active'] = isset($post['active']) ? $post['active'] : 0;
		$q = "INSERT INTO `" . $this->table . "` SET `name`='" . $post['name'] . "', `email`='" . $post['email'] . "', `active`='" . $post['active'] . "' ";
		Cms::$db->insert($q);
		return true;
	}

	function edit($post) {
		$post = maddslashes($post);
		$post['active'] = isset($post['active']) ? $post['active'] : 0;
		$q = "UPDATE `" . $this->table . "` SET `name`='" . $post['name'] . "', `email`='" . $post['email'] . "', `active`='" . $post['active'] . "' ";
		$q.= "WHERE `id`='" . (int) $post['id'] . "' ";
		Cms::$db->update($q);
		return true;
	}

	function deleteOld($id) {
		$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
		Cms::$db->delete($q);
		return true;
	}

}
