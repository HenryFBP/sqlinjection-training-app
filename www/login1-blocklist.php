<?php
ob_start();
session_start();
include("db_config.php");
ini_set('display_errors', 1);

// use a blocklist to remove dangerous characters
$BAD_SQL_CHARS = array("'", '"');
// with this simpler blocklist, we can perform fragmented sql injection.
// SELECT * from tbl_admin where username='\' and password=' and updatexml(null, concat(0x3a, version() ) ,null) -- -' limit 0,1



//$BAD_SQL_CHARS = array("'", '"', "(", ")");

function precode_block($s)
{
	return "<pre><code>" . $s . "</code></pre>";
}

function echoif($s, $b)
{
	if ($b) {
		echo $s;
	}
}

function blocklist_check($str, $blocklist)
{
	$i = 0;
	foreach ($blocklist as $char) {

		$found = false;
		if (strpos($str, $char) != false) {
			echo "Found character: '" . $char . "' in the following string: <br>";
			echo precode_block($str) . "<br>";
			$found = true;
		}

		$str = str_replace($char, "", $str);


		if ($found) {
			echo "Updated string: <br>";
			echo precode_block($str) . "<br><br>";
		}

		$i = $i + 1;
	}
	return $str;
}

?>

<!-- Enable debug using ?debug=true" -->
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Login Page 1, with blocklist defense - SQL Injection Training App</title>

	<link href="./css/htmlstyles.css" rel="stylesheet">
</head>

<body>
	<div class="container-narrow">

		<div class="jumbotron">
			<p class="lead" style="color:white">
				Login Page 1, with blocklist defense - Simple Login Bypass
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
					Password: <input type="password" id="password" name="password"><br /></br />
					Third param: <input type="text" id="thirdparam" name="thirdparam">
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
					$thirdparam = $_REQUEST['thirdparam'];

					echo "Using this blocklist to attempt to mitigate SQL injection: <br>";
					echo "<pre><code>";
					foreach ($BAD_SQL_CHARS as $char) {
						echo " - " . $char . "<br>";
					}
					echo "</code></pre>";

					$username = blocklist_check($username, $BAD_SQL_CHARS);
					$thirdparam = blocklist_check($thirdparam, $BAD_SQL_CHARS);


					$q = "SELECT * FROM users WHERE username='" . $username . "' AND password = '" . $pass . "' AND '' = '" . $thirdparam . "'";

					if (isset($_GET['debug'])) {
						if ($_GET['debug'] == "true") {
							$msg = "<div style=\"border:1px solid #4CAF50; padding: 10px\">" . $q . "</div><br />";
							echo $msg;
						}
					}

					if (!mysqli_query($con, $q)) {
						echo 'Error: ' . mysqli_error($con);
					} else {

						$result = mysqli_query($con, $q);

						// if (!$result) {
						//    		printf("%s\n", mysqli_error($con));
						//    		echo "error";
						// }

						if (mysqli_warning_count($con)) {
							$e = mysqli_get_warnings($con);
							if ($e) {
								do {
									echo "Warning: $e->errno: $e->message\n";
								} while ($e->next());
							}
						}

						echo "<br /><br />";
						$row = mysqli_fetch_array($result);


						if ($row) {
							//$_SESSION["id"] = $row[0];
							$_SESSION["username"] = $row[1];
							$_SESSION["name"] = $row[3];
							//ob_clean();

							echo "Welcome, " . $_SESSION['name'] . "! You are logged in!";

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