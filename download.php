<?php
if (isset($_GET['f'])){
	
	require('midi.class.php');
	
	//$srcFile = $_GET['f'];
	$srcFile = '_midi/'.basename($_GET['f'],'.mid').'.mid';
	
	$destFilename  = 'output.mid';
	
	$midi = new Midi();
	$midi->downloadMidFile($destFilename, $srcFile);
}
?>
