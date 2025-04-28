<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html;">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?=APP_NAME?></title>

        <link rel="icon" href="<?=BASE_URL_ASSETS_IMG?>logo-single-2025.ico" type="image/x-icon"/>
        <link rel="stylesheet" href="<?=BASE_URL_ASSETS_CSS?>bootstrap.min.css" rel="stylesheet" type="text/css">
	</head>
	<body id="mainbody">
		<div class="content-body m-0 p-0">
			<div class="login_wrapper mt-0">
				<section class="login_content text-center pt-5" id="center_content">
					<h3><?=APP_NAME?></h3>
					<img src="<?=BASE_URL_ASSETS_IMG?>loader.gif"/>
					<p id="loadtext">Checking session...</p>
				</section>
			</div>
		</div>
	</body>
    <script>
    	var baseURL         =	'<?=BASE_URL?>',
            username        =   '<?=$username?>',
            destinationMenu =   '<?=$destinationMenu?>',
            parameters      =   '<?=$parameters?>';
    </script>
	<script src="<?=BASE_URL_ASSETS_JS?>moment.min.js?<?=date('YmdHis')?>"></script>
	<script src="https://momentjs.com/downloads/moment-timezone-with-data.js"></script>
	<script src="<?=BASE_URL_ASSETS_JS?>ubid-0.1.2.min.js"></script>
	<script src="<?=BASE_URL_ASSETS_JS?>define.js?<?=date('YmdHis')?>"></script>
	<script src="<?=BASE_URL_ASSETS_JS?>jquery.min.js"></script>
	<script src="<?=BASE_URL_ASSETS_JS?>loginRedirect.js"></script>
</html>