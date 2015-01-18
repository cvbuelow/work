<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php if(isset($page_title)) echo $page_title . ' &middot; '; ?>Michigan Space Grant Consortium</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

        <link rel="stylesheet" href="<?php echo site_url(); ?>libraries/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo site_url(); ?>libraries/bootstrap/css/bootstrap-responsive.min.css">
        <link rel="stylesheet" href="<?php echo site_url(); ?>libraries/bootstrap-datepicker/css/datepicker.css">
        <link rel="stylesheet" href="<?php echo site_url(); ?>css/main.css">
        <script src="<?php echo site_url(); ?>js/vendor/modernizr-2.6.2.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->
        <div class="container">
			<div class="navbar">
				<div class="navbar-inner">
					<a class="brand" href="<?php echo site_url(); ?>">MSGC</a>
					<?php if($this->flexi_auth->is_logged_in()) : ?>
						<ul class="nav">
							<?php echo nav_item('fellowship/apply/grad', 'Graduate Fellowship Program'); ?>
							<?php echo nav_item('fellowship/apply/undergrad', 'Undergraduate Fellowship Program'); ?>
							<?php echo nav_item('program/apply', 'Program Awards'); ?>
							<?php echo nav_item('seed_grant/apply', 'Research Seed Grant'); ?>
						</ul>
						<ul class="nav pull-right">
							<?php echo nav_item('auth/logout', 'Logout'); ?>
						</ul>
					<?php else : ?>
						<ul class="nav">
							<?php echo nav_item('auth/register_account', 'Register New Account'); ?>
						</ul>
						<ul class="nav pull-right">
							<?php echo nav_item('auth', 'Login'); ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
			<?php if(isset($page_title)) : ?>
				<div class="page-header">
					<h1><?php echo $page_title; ?></h1>
				</div>
			<?php endif; ?>
			<?php if (! empty($message)) : ?>
				<div class="alerts">
					<?php echo $message; ?>
				</div>
			<?php endif; ?>
			<?php echo $content; ?>
		</div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="<?php echo site_url(); ?>js/vendor/jquery-1.9.1.min.js"><\/script>')</script>
        <script src="<?php echo site_url(); ?>libraries/bootstrap/js/bootstrap.min.js"></script>
        <script src="<?php echo site_url(); ?>libraries/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
        <script src="<?php echo site_url(); ?>js/plugins.js"></script>
        <script src="<?php echo site_url(); ?>js/main.js"></script>

        <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
        <script>
            var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src='//www.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>
    </body>
</html>
