<?php
session_start();//to make sure all session is destroyed
	        //turn on Addtype application/x-php	.html on your apache config
session_destroy();
require_once('config/connection.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ERP SYSTEM Login</title>

	<!-- Google Fonts - Inter -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link rel="stylesheet" type="text/css" href="style/generic.css">
	<link rel="stylesheet" type="text/css" href="style/bootstrap-custom.css">

	<!-- Favicon -->
	<link rel="shortcut icon" href="images/logoagro.png">

	<!-- JavaScript -->
	<script language="JavaScript1.2" src="js/generic.js"></script>
	<script language="JavaScript1.2" src="js/drag.js"></script>
	<script language="JavaScript1.2" src="js/login.js"></script>

	<style>
		* {
			font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
		}

		body {
			background: linear-gradient(135deg, #E8F4F4 0%, #CFE9FA 100%);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
		}

		.login-card {
			max-width: 450px;
			width: 100%;
			box-shadow: 0 10px 40px rgba(0,0,0,0.1);
			border-radius: 15px;
			overflow: hidden;
		}

		.login-header {
			background: linear-gradient(135deg, #275370 0%, #3E71B2 100%);
			color: white;
			padding: 2rem;
			text-align: center;
		}

		.login-body {
			background: white;
			padding: 2rem;
		}

		.form-control:focus {
			border-color: #275370;
			box-shadow: 0 0 0 0.2rem rgba(39, 83, 112, 0.25);
		}

		.btn-login {
			background: linear-gradient(135deg, #275370 0%, #3E71B2 100%);
			border: none;
			color: white;
			padding: 0.75rem;
			font-weight: bold;
			text-transform: uppercase;
			letter-spacing: 1px;
			transition: all 0.3s;
		}

		.btn-login:hover {
			transform: translateY(-2px);
			box-shadow: 0 5px 20px rgba(39, 83, 112, 0.3);
			color: white;
		}

		.owl-bg {
			display: none !important; /* Hidden */
		}

		.company-logo {
			display: none !important; /* Hidden */
		}

		.footer-custom {
			display: none !important; /* Hidden footer */
		}

		@keyframes fadeInDown {
			from {
				opacity: 0;
				transform: translateY(-20px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.login-card {
			animation: fadeInUp 0.8s;
		}

		@keyframes fadeInUp {
			from {
				opacity: 0;
				transform: translateY(30px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}
	</style>
</head>
<body>

<img src="images/OWL_OV.png" class="owl-bg" alt="Background">

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-6 col-lg-5">
			<div class="login-card">
				<div class="login-header">
					<div class="company-logo">
						<img src="images/logoagro.png" alt="Logo" style="height: 60px;">
					</div>
					<h4 class="mb-1">ERP SYSTEM</h4>
				</div>

				<div class="login-body">
					<div id="msg" class="mb-3"></div>

					<form onsubmit="return false;">
						<div class="mb-3">
							<label for="name" class="form-label">
								<i class="bi bi-person-fill"></i> Username
							</label>
							<input type="text"
								   class="form-control"
								   id="name"
								   placeholder="Enter your username"
								   onkeypress="return enter(event);"
								   title="Case-sensitive"
								   autocomplete="username"
								   required>
						</div>

						<div class="mb-3">
							<label for="pwd" class="form-label">
								<i class="bi bi-lock-fill"></i> Password
							</label>
							<input type="password"
								   class="form-control"
								   id="pwd"
								   placeholder="Enter your password"
								   onkeypress="return enter(event);"
								   title="Case-sensitive"
								   autocomplete="current-password"
								   required>
						</div>

						<div class="mb-4">
							<label for="language" class="form-label">
								<i class="bi bi-globe"></i> Language
							</label>
							<select id="language" class="form-select">
<?php
$str="select * from ".$dbname.".namabahasa order by code";
$res=mysql_query($str);

echo mysql_error($conn);
while($bar=mysql_fetch_object($res))
{
  echo "<option value='".$bar->code."'";
  # Default Language
  if($bar->code=='ID') {
	echo " selected";
  }
  echo ">".$bar->name."</option>";
}
?>
							</select>
						</div>

						<div class="d-grid">
							<button type="button" class="btn btn-login btn-lg" onclick="login()">
								<i class="bi bi-box-arrow-in-right"></i> Login
							</button>
						</div>
					</form>

					<div class="text-center mt-4">
						<small class="text-muted">
							&copy; <?php echo date('Y'); ?> ERP SYSTEM
						</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id='progress' style='display:none;position:fixed;top:20px;right:20px;'>
	<div class="alert alert-info" role="alert">
		<div class="d-flex align-items-center">
			<div class="spinner-border spinner-border-sm me-2" role="status">
				<span class="visually-hidden">Loading...</span>
			</div>
			<div>Please wait...</div>
		</div>
	</div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Auto-focus on username field
document.addEventListener('DOMContentLoaded', function() {
	document.getElementById('name').focus();
});

// Update progress indicator
function showProgress() {
	document.getElementById('progress').style.display = 'block';
}

function hideProgress() {
	document.getElementById('progress').style.display = 'none';
}
</script>

</body>
</html>
