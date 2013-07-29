<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Stocktake</title>
</head>
<body>

<div id="message" align="center"><?php echo $message; ?></div>

<form id="frmLogin" name="frmLogin" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<table border="1" align="center">
		<thead>
			<tr>
				<th colspan="2">Login</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Username</td>
				<td><input type="text" id="username" name="username" value="" /></td>
			</tr>
			<tr>
				<td>Password</td>
				<td><input type="password" id="password" name="password" value="" /></td>
			</tr>
			<tr>
				<td colspan="2">
					<div align="center">
						<input type="submit" id="submit" name="submit" value="Submit" />
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</form>

</body>
</html>