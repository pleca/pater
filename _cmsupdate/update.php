<?php

ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('date.timezone', 'Europe/London');
date_default_timezone_set('Europe/London');
define('DR',$_SERVER["DOCUMENT_ROOT"]);

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "errorHandling.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "classCmsUpdate.php");

$objUpdate = new classCmsUpdate();

/*
 * wywolanie: /_cmsupdate/update.php
 * pliki pobierane sa z FTP CENTRAL
 * z katalogu: private/updates/4me.cms
 * 
 */

/*
 * Biblioteka pobiera TYLKO ostatnia aktualizacje.
 * W przypadku kiedy na FTP beda lezec dwa pliki
 * update1.zip i update2.zip
 * Zostanie pobrany tylko update2.zip
 * a update1.zip zostanie calkowicie zignorowany
 * rowniez w przyszlych aktualizacjach
 * 
 * Bierz to pod uwage jak wrzucisz jedna aktualizacje
 * po czym stwierdzisz, ze chcesz dodac jeszcze dwa pliki
 * i umiescisz tylko te dwa pliki w drugiej aktualizacji
 * to TYLKO one zostana zaktualizowane a wczesniejszy update zignorowany
 * 
 * Kazda nowa aktualizacja ma miec komplet plikow ktore chcesz zaktualizowac
 * nie mozna rozbijac aktualizacji na dwa pliki .zip
 * 
 * Jest to zabezpieczenie przed tym, zeby w nastepnych systemach gateway
 * jak rowniez gdyby ktos usunal historie pobran (updates.db)
 * aktualizator nie pobieral z FTP starych aktualizacji
 * 
 */

/*
 * Plik SQL ktory dolaczysz do paczki absolutnie MUSI spelniac te wymagania:
 * 
 * 1. Kazda instrukcja musi sie konczyc srednikiem (;) i znakiem nowej linii
 * nie moze wystapic sytuacja w ktorej instrukcja konczy sie srednikiem po ktorym
 * jest np spacja i dopiero znacznik nowej linii bo caly SQL sie rozsypie
 * 
 * 2. W pliku nie moze byc nic innego niz tylko zapytania SQL. Zadnych komentarzy
 * 
 */

/*
 * Niemal kazde niepowodzenie powinno zostawic cos w logach.
 * Dlatego jak cos pojdzie nie tak raczej na pewno w logach
 * bedzie informacja co dokladnie zawinilo
 * 
 * Sprawdzaj logi
 * 
 * Plik z logami do aktualizacji ma w nazwie _UPDATE_.txt
 * 
 * Przynajmniej przez pierwszych kilka aktualizacji warto zawsze sprawdzac logi
 * bo nie wszystko moze dzialac tak jak powinno
 * 
 */

// nazwa pliku sql ktory bedzie sie znajdowal w glownym katalogu
$UDB = 'update_db.sql';

// check update niczego nie pobiera z ftp, dziala wzglednie szybko
// pobiera z serwera tylko liste nazw plikow i na jej podstawie stwierdza
// czy jest jakas nowa aktualizacja

$r = 'SUCCESS';	// odpowiedz do przegladarki

if($objUpdate->checkUpdate()) {
	
	// pobieranie moze troche trwac
	// w logach przy informacji DOWNLOADING COMPLETE
	// powinna byc informacja ile trwalo pobieranie
	// jak cos bedzie nie tak z aktualizacja
	// istnieje duza szansa, ze skrypt zostal ubity za zbyt dlugie dzialanie
	// trzeba to monitorowac na poczatku zeby wiedziec na jakiej wielkosci
	// pliki mozna sobie pozwalac. Ewentualnie zwiekszyc czas skryptowi
	
	if($objUpdate->donwloadUpdate()) {
		
		if($objUpdate->unpackUpdate()) {
			
			// pliki/foldery ktore nie beda nadpisywane
			//$objUpdate->skipFile("config.php");
			//$objUpdate->skipFile("folder_do_pominiecia");
			
			$objUpdate->skipFile($UDB);
			if(!$objUpdate->doUpdate()) {
			
				$r = 'UPDATE FILES ERROR';
			}
			$objUpdate->setUpdateDBFilename($UDB);
			if(!$objUpdate->doUpdateDB()) {
				
				$r = 'UPDATE DATABASE ERROR';
			}
		} else {

			$r = 'UNPACK ERROR';
		}		
	} else {

		$r = 'DOWNLOAD ERROR';
	}	
} else {

	$r = 'NO NEW UPDATES';
}

$objUpdate->logUpdate($r);
$objUpdate->cleanupUpdate();


echo $r;