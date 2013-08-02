<?php
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment; filename='.$_GET['xlsname']);
	echo file_get_contents($_GET['xlsfile']);
?>