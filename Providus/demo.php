			  
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>App</title>
	<link href='//fonts.googleapis.com/css?family=Bungee+Hairline|Bungee+Inline' rel='stylesheet'>
	<style>
	input[type=text], select {
		width: 100%;
		padding: 12px 20px;
		margin: 8px 0;
		display: inline-block;
		border: 1px solid #ccc;
		border-radius: 4px;
		box-sizing: border-box;
	}

	input[type=submit] {
		width: 100%;
		background-color: #ECB62F;
		color: white;
		padding: 14px 20px;
		margin: 8px 0;
		border: none;
		border-radius: 4px;
		cursor: pointer;
	}

	input[type=submit]:hover {
		background-color: #34424B;
	}
	
	b#logout{
		width: 10%;
		background-color: #ECB62F;
		color: white;
		padding: 4px ;
		margin: 8px 0;
		border: none;
		border-radius: 4px;
		cursor: pointer;
		font-family:'Bungee Inline';
		float: left;
	}
	
	b#logout:hover {
		background-color: #34424B;
	}

	div {
		border-radius: 5px;
		background-color: #f2f2f2;
		padding: 20px;
			
	}
	
	label{
	font-family:'Bungee Inline';
	
}

input#print{
		width: 40%;
		background-color: #ECB62F;
		color: white;
		padding: 4px 20px;
		margin: 8px 0;
		border: none;
		border-radius: 4px;
		cursor: pointer;
		font-family:'Bungee Inline';
		float: right;
	}
	
	input#print:hover {
		background-color: #34424B;
	}

	table {
    border-collapse: collapse;
}

table, th, td {
    border: 1px solid black;
}
th {
    text-align: left;
}
tr:hover {background-color: #f5f5f5}

th, td {
    padding: 15px;
    text-align: left;
}
	</style>
	
	</script>
</head>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 'Off');

if (!isset($_SESSION)) session_start();
if (isset($_POST['submit'])) {
    $username = $_POST['userName'];
    $url = "insert your own URL";
    $client = new SoapClient($url);
    $res = $client->GetDraftForPrinting(array("RefNum" => $username)); // "enter reference number"
    $array1 = (array) $res;
    if (count($array1) == 0) {
        echo "Wrong or no number Entered. Please try again.";
    } else {
        $array2 = (array) $array1['GetDraftForPrintingResult'];
        $_SESSION['json_string'] = json_encode($array2);
		
		$tempArray[] = json_decode($_SESSION['json_string'],true);
		$myarray1 = $tempArray[0];
?>		
	<table>

<tr>
    <th>Reference Number</th>
    <th>Issue Date</th>
    <th>Draft Amount</th>
	<th>Beneficiary Name</th>
	<th>Amount in Words</th>
</tr>
      <?php
	   //Error here 
     
        echo '<tr>';
        echo '<td>' . $myarray1['Ref_Num'] . '</td>';
        echo '<td>' . $myarray1['Issue_Date'] . '</td>';
        echo '<td>' . $myarray1['Drft_Amt'] . '</td>';
		echo '<td>' . $myarray1['Ben_Name'] . '</td>';
		echo '<td>' . $myarray1['AmountInWords'] . '</td>';
        echo '</tr>';
  
    }
} else if (isset($_POST['print'])) {
	
	$array3[]= json_decode($_SESSION['json_string'],true);
	$myarray = $array3[0];
	
	try {
		$mysqli = new mysqli("localhost", " ", " ");
		} catch (\Exception $e) {
		echo $e->getMessage(), PHP_EOL;
		}
		if ($mysqli->select_db('query_databases') == false) {
			$sql = "CREATE DATABASE query_databases";
			$mysqli->query($sql);
			$mysqli->select_db('query_databases');
		}
		$table = 'QueryTable';

		$result2 = $mysqli->query( "SELECT * FROM ".$table." WHERE transID = ".$myarray['Ref_Num']);
		
		if ($result2 ->num_rows != 0) {
			echo "This number has already been used and printed before";
		}
		
			
		
		else{
			
			$mysqli->select_db('query_databases');
		
		
			$table = 'QueryTable';
			$result = $mysqli->query("SHOW TABLES LIKE ".$table);
				if($result !== false) {
					$mysqli->query("INSERT INTO ".$table." (userID, transID)
					VALUES ('".$_SESSION['login_user']."',".$myarray['Ref_Num'].")");
				}
				
			else {
				$sql = "CREATE TABLE ".$table." (
				id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
				userID VARCHAR(30) NOT NULL,
				transID INT(30) NOT NULL,
				PrintDate timestamp default now()
				)";
					
				$mysqli->query($sql);
				$mysqli->query("INSERT INTO ".$table." (userID, transID)
					VALUES ('".$_SESSION['login_user']."',".$myarray['Ref_Num'].")");
			}
			
			//Print
			$array3[]= json_decode($_SESSION['json_string'],true);
			$handle = printer_open("EPSON LQ-350 ESC/P2");
			printer_start_doc($handle, "Doc");
			printer_start_page($handle);

			// print
			$font = printer_create_font("Gotham", 23, 10, 400, false, false, false, 0);
			printer_select_font($handle, $font);
			$myarray = $array3[0];
			if(strlen($myarray['Ben_Name'])>=40){
				$font = printer_create_font("Gotham", 23, 10, 400, false, false, false, 0);
				printer_select_font($handle, $font);
				printer_draw_text($handle,$myarray['Ben_Name'],37,174);}
			else{
				printer_draw_text($handle,$myarray['Ben_Name'],40,174);}
			$font = printer_create_font("Gotham", 23, 10, 400, false, false, false, 0);
			printer_select_font($handle, $font);
			printer_draw_text($handle,$myarray['Issue_Date'],850,170);
			
			if(strlen($myarray['AmountInWords'])> 40){
				$myarraytemp =explode("\n",wordwrap($myarray['AmountInWords'],40));
				
				$y = 240;
				for($i =0; $i<=count($myarraytemp); $i++){
					
					global $y;
					$font = printer_create_font("Gotham",23, 10, 400, false, false, false, 0);
					printer_select_font($handle, $font);
					printer_draw_text($handle,'**'.$myarraytemp[$i],100,$y);
					$y += 77;
					
					}
				}
			else{
				printer_draw_text($handle,'**'.$myarray['AmountInWords'],100,240);
				}
			
			$font = printer_create_font("Gotham", 28, 14, 400, false, false, false, 0);
			printer_select_font($handle, $font);
			
			printer_draw_text($handle,'**'.$myarray['Drft_Amt'].'**',840,300);
			printer_draw_text($handle,$myarray['Ref_Num'],170,500);
			
			
			// This is where $result should go

			printer_end_page($handle);
			printer_end_doc($handle);
			printer_close($handle);
			
			
		}
		
		$mysqli->close();
		
		unset($_SESSION['json_string']);
		
	}
		
		
else {
    unset($_SESSION['json_string']);
}
?>

<body>

<div >
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method = "post" >
<input type="hidden" name="func_name" value="SEND_REFERENCE">
 
    <br><label> REFERENCE NO: </label></br>
  <input type="text" name="userName" />
  
  <input name = 'submit' type="submit" value="Submit" >
  <?php
  if (isset($_SESSION['json_string'])) {
  ?>
  <br />
    <input id = 'print' name = 'print' type="submit" value="print"/>
  <?php
  }
  ?>
</form> 
</div>


<b id="logout"><a href="logout.php">Log Out</a></b>
</body>
</html>
