<?php
//
// jQuery File Tree PHP Connector
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//

require WEBSITE_DOCROOT . '/inc/loadAll.php';
$quizSite = new QuizSite();

$siteFolder = WEBSITE_DOCROOT . '/sites/' . $quizSite->siteid . '/';

if(!is_dir($siteFolder)) {
  mkdir($siteFolder . '.trash/', 0777, true);
}

$excludedList = array('.', '..', '.trash', '.quarantine', '.tmb');

if( file_exists($siteFolder) ) {
	$files = scandir($siteFolder);
	natcasesort($files);
  $fileCount = 0;
	if( count($files) > 2 ) { /* The 2 accounts for . and .. */
		echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		// All dirs
		foreach( $files as $file ) {
			if( file_exists($siteFolder . $file) && !in_array($file, $excludedList) && is_dir($siteFolder . $file) ) {
        $fileCount++;
				echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($file) . "/\">" . htmlentities($file) . "</a></li>";
			}
		}
		// All files
		foreach( $files as $file ) {
			if( file_exists($siteFolder . $file) && !in_array($file, $excludedList) && !is_dir($siteFolder . $file) ) {
				$ext = preg_replace('/^.*\./', '', $file);
        $fileCount++;
				echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($file) . "\">" . htmlentities($file) . "</a></li>";
			}
		}
		echo "</ul>";	
	}
  if($fileCount <= 0) {
    echo '<p>No files available.</p>';
  }
}

?>