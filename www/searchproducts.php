<?php
ob_start();
session_start();
include("db_config.php");
if (!$_SESSION["username"]) {
	header('Location:login1.php?msg=1');
}
ini_set('display_errors', 1);

function get_product_names_array($connection)
{
	$list_of_products = array();

	$q = "SELECT product_name FROM products";

	$result = mysqli_query($connection, $q);
	while ($row = mysqli_fetch_array($result)) {
		array_push($list_of_products, $row[0]);
	}

	return $list_of_products;
}

?>
<!-- Enable debug using ?debug=true" -->
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Search Products - SQL Injection Training App</title>

	<link href="./css/htmlstyles.css" rel="stylesheet">
</head>

<body>
	<script>
		function simulate_xss() {
			alert('todo');
		}
	</script>

	<div class="container-narrow">

		<div class="jumbotron">
			<p class="lead" style="color:white">
				Welcome <?php echo $_SESSION["username"]; ?>!! Search for products here</a>
			</p>
		</div>

		<div class="response">

			<p style="color:white">
			<table class="response">
				<form method="POST" autocomplete="off">

					<tr>
						<td>
							Search for a product:
						</td>
						<td>
							<input type="text" id="searchitem" name="searchitem">&nbsp;&nbsp;
						</td>
						<td>
							<input type="submit" value="Search!" />
						</td>
					</tr>
			</table>

			</p>

			</form>
		</div>
		<div class="response">

			<p style="color:white">
			<table class="response">
				<form method="POST" autocomplete="off">

					<tr>
						<td>
							Search for a product using drop-down:
						</td>
						<td>
							<select name="searchitem">

								<?php
								$products = get_product_names_array($con);

								foreach ($products as $product) {
									echo "<option value='" . $product . "'>" . $product . "</option>\n";
								}

								?>

								<option value="test">test</option>
							</select>
						</td>
						<td>
							<input type="submit" value="Search!" />
						</td>
						<td>
							<button onclick="simulate_xss()">Simulate XSS</button>
						</td>
					</tr>
			</table>

			</p>

			</form>
		</div>


		<br />

		<?php
		if (isset($_POST["searchitem"])) {

			$q = "Select * from products where product_name like '" . $_POST["searchitem"] . "%'";

			if (isset($_GET['debug'])) {
				if ($_GET['debug'] == "true") {
					$msg = "<div style=\"border:1px solid #4CAF50; padding: 10px\">" . $q . "</div><br/>";
					echo $msg;
				}
			}
		}

		?>

		<div class="searchheader" style="color:white">
			<table>

				<tr>
					<td style="width:200px ">
						<b>Product Name</b>
					</td>

					<td style="width:200px ">
						<b>Product Type</b>
					</td>

					<td style="width:450px ">
						<b>Description</b>
					</td>

					<td style="width:110px ">
						<b>Price (in USD)</b>
					</td>

				</tr>

				<?php

				if (isset($_POST["searchitem"])) {
					$result = mysqli_query($con, $q);
					if (!$result) {
						echo ("</table></div>" . mysqli_error($con));
					} else {

						while ($row = mysqli_fetch_array($result)) {
							echo "<tr><td style=\"width:200px\">" . $row[1] . "</td><td style=\"width:200px\">" . $row[2] . "</td><td style=\"width:450px\">" . $row[3] . "</td><td style=\"width:110px\">" . $row[4] . "</td></tr>";
						}
					}
				}
				?>
			</table>
		</div>



		<div class="footer">
			<p>
			<h4><a href="blindsqli.php?user=<?php echo $_SESSION['username']; ?>">Profile</a> | <a href="logout.php">Logout</a> | <a href="index.php">Home</a>
				<h4>
					</p>
		</div>


		<div class="footer">
			<p><a href="https://appsecco.com">Appsecco</a> | Riyaz Walikar | <a href="https://twitter.com/riyazwalikar">@riyazwalikar</a></p>
		</div>

	</div> <!-- /container -->

</body>

</html>