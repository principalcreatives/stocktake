<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Stocktake</title>
	
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript">

		$(document).ready(function(){
			$("#submit").submit(function(){
				//alert("asdfasdf");
			});
		});
		
	</script>
	
</head>
<body>

<div id="container">

	<div id="top"><h4>Hi <?= $initials ?>, <a href="logout.php">Logout</a></h4></div>
	
	<h1>Open Stocktakes</h1>
	
	<table border="1" align="center">
		<thead>
			<tr>
				<th>Session #</th>
				<th>Date</th>
				<th>Location</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach($rs_stocktake as $key => $value){
			?>
			<tr>
				<td>
					<a href="view_stocktake.php?id=<?= $rs_stocktake[$key]["SessionNo"] ?>"><?php print $rs_stocktake[$key]['SessionNo']; ?></a>
				</td>
				<td><?php print $rs_stocktake[$key]['SessionDate']->format('Y-m-d H:i:s'); ?></td>
				<td><?php print $rs_stocktake[$key]['LocCode']; ?></td>
			</tr>
			<?php 
			}
			?>
		</tbody>
	</table>
	
	<h1>Add Stocktakes</h1>
	
	<div id="message"><?= $message ?></div>
	
	<form id="frmAddStocktake" name="frmAddStocktake" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
		<table border="1" align="center">
			<tbody>
				<tr>
					<td>Branch</td>
					<td>
						<select id="branch" name="branch">
							<option value="select">--------------------</option>
							<?php foreach ($rs_location as $key => $value){ ?>
							<option value="<?php echo $rs_location[$key]["LocNo"]; ?>"><?php echo $rs_location[$key]["LocCode"]; ?></option>
							<?php } ?>
						</select>
					</td>
					<td>
						<div align="right"><input type="submit" id="submit" name="submit" value="Add" /></div>
					</td>
				</tr>
			</tbody>
		</table>
	</form>

</div><!-- containter -->

</body>
</html>


