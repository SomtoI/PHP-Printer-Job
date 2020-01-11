<?php
$dataPOST = trim(file_get_contents('php://input'));
$xmlData = simplexml_load_string($dataPOST);

echo 'received';
 ?>