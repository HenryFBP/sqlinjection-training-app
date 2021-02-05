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
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<link href="./css/htmlstyles.css" rel="stylesheet">
</head>

<body>
	<script>
			//obfuscated version of the below "XSS" attack
			function simulate_xss_obfuscated() {
				eval(atob('CQkJCWNoaWxkcmVuID0gJCgnc2VsZWN0JykuY2hpbGRyZW4oKQoKCQkJCWZvciAobGV0IGkgPSAwOyBpIDwgY2hpbGRyZW4ubGVuZ3RoOyBpKyspIHsKCQkJCQljaGlsZCA9IGNoaWxkcmVuW2ldOwoJCQkJCWNvbnNvbGUubG9nKGNoaWxkKTsKCgkJCQkJY2hpbGQuc2V0QXR0cmlidXRlKCJ2YWx1ZSIsICQoJy5wYXlsb2FkJykudmFsKCkpOwoKCQkJCX0KCQkJCQoJCQkJJCgnLmRyb3Bkb3duLXN1Ym1pdC1idXR0b24nKS5hdHRyKCd2YWx1ZScsICdTZWFyY2ghIChpbmplY3RlZCknKQ=='));
			}

			function simulate_xss() {

				children = $('select').children()

				for (let i = 0; i < children.length; i++) {
					child = children[i];
					console.log(child);

					child.setAttribute("value", $('.payload').val());

				}
				
				$('.dropdown-submit-button').attr('value', 'Search! (injected)')

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
							<input type="submit" class="dropdown-submit-button" value="Search!" />
						</td>
					</tr>
			</table>

			</p>

			</form>
		</div>

		<div>
			<button onclick="simulate_xss_obfuscated()">Simulate XSS</button>
			<br>
			SQLi payload to inject into all list items: (try this:) 
			<pre><code>' or 1=1;-- //</code></pre>
			<input type="text" class="payload"></input>
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