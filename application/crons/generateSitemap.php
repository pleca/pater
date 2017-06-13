<?php

generateSiteMap();   // generujemy sitemap.xml

function generateSiteMap() {

   foreach(Cms::$langs as $v) {
	   
		$xmlTxt = '<?xml version="1.0" encoding="UTF-8"?>
		<urlset
		   xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
		   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		   xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
				 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
		';

		if ($v['default'] != 1) { 
			$lang = '/'.$v['code'];
		} else { 
			$lang = ''; 			
		}		
		
		$xmlTxt.= loadMain($lang, '1.0');
		
		if (CMS::$modules['shop']) {
			$xmlTxt.= loadProducts($lang, $v['code'], '0.9');
		}

		if (CMS::$modules['menu']) {
			$xmlTxt.= loadMenu($lang, $v['code'], '0.8');
		}
		
		if (CMS::$modules['pages']) {
			$xmlTxt.= loadXml($lang, $v['code'], 'pages', '', '0.7');   // table, url, priority
		}

//		   if($aModules['gallery'] == 1) $xmlTxt.= loadXml($lang, $v['id'], 'gallery', 'gallery', '0.6');
		   
		   if (CMS::$modules['news']) {
			   $xmlTxt.= loadXml2($lang, $v['id'], 'news', 'news', '0.5');
		   }
		   
//		   if($aModules['articles'] == 1) $xmlTxt.= loadXml($lang, $v['id'], 'articles', 'articles', '0.4');   
//		   
		
		   $xmlTxt.= '
	</urlset>';

      $file=fopen(CMS_DIR.'/public/sitemap/'.$v['code'].'.xml', 'w');
      fwrite($file, $xmlTxt);
      fclose($file);
   }
   return true;
}

function loadMain($lang, $priority = '0.5') {
	$lastmod = date('Y-m-d').'T'.date('H:i:s').'+00:00';
	$changefreq = 'daily';
	$items = '
		<url>
		<loc>'.SERVER_URL.CMS_URL.$lang.'/</loc>
		<priority>'.$priority.'</priority>
		<lastmod>'.$lastmod.'</lastmod>
		<changefreq>'.$changefreq.'</changefreq>
		</url>';
	return $items;
}

function loadProducts($lang, $lang_code, $priority = '0.5') {
	$lastmod = date('Y-m-d').'T'.date('H:i:s').'+00:00';
	$changefreq = 'daily';

   $q = "SELECT p.*, pt.slug, pt.content_short, ct.slug as category_url, ";
   $q.= "(SELECT `slug` FROM `".DB_PREFIX."categories_translation` WHERE translatable_id=c.parent_id LIMIT 1) as parent_url ";
   $q.= "FROM `".DB_PREFIX."product` p LEFT JOIN `".DB_PREFIX."product_translation` pt ON p.id=pt.translatable_id ";
   $q.= "LEFT JOIN `".DB_PREFIX."categories` c ON p.category_id=c.id ";
   $q.= "LEFT JOIN `".DB_PREFIX."categories_translation` ct ON ct.translatable_id=c.id ";
   $q.= "WHERE pt.locale='".$lang_code."' AND ct.locale='".$lang_code."' AND p.status_id IN (1,2) ";
   $q.= "GROUP BY p.id ORDER BY pt.name ASC ";

   $array = Cms::$db->getAll($q);

   $items = '';
   foreach($array as $v)
   {
      if($v['parent_url']) $url = SERVER_URL.CMS_URL.$lang.'/'.$v['parent_url'].'/'.$v['category_url'].'/'.$v['slug'].'.html';
      else $url = SERVER_URL.CMS_URL.$lang.'/'.$v['category_url'].'/'.$v['slug'].'.html';
      $items.= '
<url>
<loc>'.$url.'</loc>
<priority>'.$priority.'</priority>
<lastmod>'.$lastmod.'</lastmod>
<changefreq>'.$changefreq.'</changefreq>
</url>';
   }
   return $items;
}

function loadMenu($lang, $lang_code, $priority = '0.5') {

   $lastmod = date('Y-m-d').'T'.date('H:i:s').'+00:00';
   $changefreq = 'daily';   
   
   $q = "SELECT t.url, a.type FROM `".DB_PREFIX."menu` a LEFT JOIN `".DB_PREFIX."menu_translation` t ON a.id=t.translatable_id ";   
   $q.= "WHERE a.type='module' AND t.locale='".$lang_code."' AND t.url!='index' AND t.url!='' GROUP BY t.url ORDER BY t.name ASC";

   $array = Cms::$db->getAll($q);

   $items = '';
   foreach($array as $v)
   {
      $url = SERVER_URL.CMS_URL.$lang.'/'.$v['url'].'.html';
      $items.= '
<url>
<loc>'.$url.'</loc>
<priority>'.$priority.'</priority>
<lastmod>'.$lastmod.'</lastmod>
<changefreq>'.$changefreq.'</changefreq>
</url>';
   }
   return $items;
}

function loadXml($lang, $lang_code, $table, $link, $priority = '0.5') {
   $lastmod = date('Y-m-d').'T'.date('H:i:s').'+00:00';
   $changefreq = 'daily';
   $q = "SELECT t.slug FROM `".DB_PREFIX.$table."` a LEFT JOIN `".DB_PREFIX.$table."_translation` t ON a.id=t.translatable_id ";
   $q.= "WHERE t.locale='".$lang_code."' AND t.slug!='' ORDER BY t.title ASC";
   $array = Cms::$db->getAll($q);

   $items = '';
   foreach($array as $v)
   {
      if($table == 'pages') $url = SERVER_URL.CMS_URL.$lang.'/'.$v['slug'].'.html';
      else $url = SERVER_URL.CMS_URL.$lang.'/'.$link.'/'.$v['slug'].'.html';
      $items.= '
<url>
<loc>'.$url.'</loc>
<priority>'.$priority.'</priority>
<lastmod>'.$lastmod.'</lastmod>
<changefreq>'.$changefreq.'</changefreq>
</url>';
   }
   return $items;	
}
		
function loadXml2($lang, $lang_id, $table, $link, $priority = '0.5')
{

   $lastmod = date('Y-m-d').'T'.date('H:i:s').'+00:00';
   $changefreq = 'daily';
   $q = "SELECT d.name_url FROM `".DB_PREFIX.$table."` a LEFT JOIN `".DB_PREFIX.$table."_desc` d ON a.id=d.parent_id ";
   $q.= "WHERE d.lang_id='".$lang_id."' AND d.name_url!='' ORDER BY d.name ASC";
   $array = Cms::$db->getAll($q);

   $items = '';
   foreach($array as $v)
   {
      if($table == 'pages') $url = SERVER_URL.CMS_URL.$lang.'/'.$v['name_url'].'.html';
      else $url = SERVER_URL.CMS_URL.$lang.'/'.$link.'/'.$v['name_url'].'.html';
      $items.= '
<url>
<loc>'.$url.'</loc>
<priority>'.$priority.'</priority>
<lastmod>'.$lastmod.'</lastmod>
<changefreq>'.$changefreq.'</changefreq>
</url>';
   }
   return $items;
}

?>