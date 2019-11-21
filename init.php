<?php 
	require_once 'functions.php';
	session_start();
	$page = detectPage();

	$db = new PDO('mysql:host=localhost;dbname=id7264102_web17ck1;charset=utf8', 'id7264102_trankhoi1403', 'phpmyadmin1403');
	$stmt = $db->query("SELECT * FROM USER");
	// Lấy hết toàn bộ dữ liệu
	$data = null;
	//$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	// hoặc lấy từng dòng
	/*while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	  echo $row['field1'] . ' ' . $row['field2']; 
	}*/

    $row = null;

    $currentUser = null;
?>