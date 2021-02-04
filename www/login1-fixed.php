<?php
ob_start();
session_start();
include("db_config.php");
ini_set('display_errors', 1);
?>

<!-- Enable debug using ?debug=true" -->
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Login Page 1 FIXED - SQL Injection Training App</title>

	<link href="./css/htmlstyles.css" rel="stylesheet">
</head>

<body>
	<div class="container-narrow">

		<div class="jumbotron">
			<p class="lead" style="color:white">
				Login Page 1 FIXED - Simple Login Bypass
				<?php
				if (!empty($_REQUEST['msg'])) {
					if ($_REQUEST['msg'] === "1") {
						$_SESSION['next'] = 'searchproducts.php';
						echo "<br />Please login to continue to Search Products";
					} elseif ($_REQUEST['msg'] === "2") {
						$_SESSION['next'] = 'blindsqli.php';
						echo "<br />Please login to continue to Blind SQL Injection Page";
					} elseif ($_REQUEST['msg'] === "3") {
						$_SESSION['next'] = 'os_sqli.php';
						echo "<br />Please login to continue to OS Command Injection Page";
					} else {
						$_SESSION['next'] = 'searchproducts.php';
					}
				}
				?>
			</p>
		</div>

		<div class="response">
			<form method="POST" autocomplete="off">
				<p style="color:white">
					Username: <input type="text" id="uid" name="uid"><br /></br />
					Password: <input type="password" id="password" name="password">
				</p>
				<br />
				<p>
					<input type="submit" value="Submit" />
					<input type="reset" value="Reset" />
				</p>
			</form>
		</div>


		<br />

		<div class="row marketing">
			<div class="col-lg-6">

				<?php
				//echo md5("pa55w0rd");

				if (!empty($_REQUEST['uid'])) {
					$username = $_REQUEST['uid'];
					$pass = md5($_REQUEST['password']);

					$sql = "SELECT username, fname FROM users where username = ? AND password = ?";

					if (isset($_GET['debug'])) {
						if ($_GET['debug'] == "true") {
							$msg = "<div style=\"border:1px solid #4CAF50; padding: 10px\">" . $sql . "</div><br />";
							echo $msg;
						}
					}
					$stmt = $con->prepare($sql);
					$stmt->bind_param("ss", $username, $pass);
					$result = $stmt->execute();



					echo "DEBUG: result = '$result'<br>";

					if (!$result) {
						echo 'Error: ' . mysqli_error($con);
					} else {

						$stmt->bind_result($returned_username, $returned_fname);

						$fetch_result = $stmt->fetch();


						if ($fetch_result) {
							echo "DEBUG: fetch_result is truth-y. This means there was a ResultSet.<br>";
						} else {
							echo "DEBUG: fetch_result is false-y. This means there was either not a ResultSet or there was an error.<br>";
						}

						echo "DEBUG: Fetch returns '$returned_username', '$returned_fname'<br>";

						if (mysqli_warning_count($con)) {
							$e = mysqli_get_warnings($con);
							if ($e) {
								do {
									echo "Warning: $e->errno: $e->message\n";
								} while ($e->next());
							}
						}

						echo "<br /><br />";

						if ($fetch_result) {
							//$_SESSION["id"] = $row[0];
							$_SESSION["username"] = $returned_username;
							$_SESSION["name"] = $returned_fname;
							//ob_clean();

							echo "Welcome, " . $_SESSION['name']."!";

							if ($_SESSION['next'] == "searchproducts.php") {
								header('Location: searchproducts.php');
							} elseif ($_SESSION['next'] == "blindsqli.php") {
								header('Location: blindsqli.php?user=' . $_SESSION["username"]);
							} elseif ($_SESSION['next'] == "os_sqli.php") {
								header('Location: os_sqli.php?user=' . $_SESSION["username"]);
							}
						} else {
							echo "<font style=\"color:#FF0000\">Invalid password!</font\>";
						}
					}
				}

				//}
				?>

			</div>
		</div>

		<div class="footer">
			<p>
			<h4><a href="index.php">Home</a>
				<h4>
					</p>
		</div>

		<div class="footer">
			<p><a href="https://appsecco.com">Appsecco</a> | Riyaz Walikar | <a href="https://twitter.com/riyazwalikar">@riyazwalikar</a></p>
		</div>
	</div> <!-- /container -->

</body>

</html>