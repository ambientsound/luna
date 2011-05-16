<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>{$path->getTitle('condensed', ' &raquo; ', 'ltr')}</title>
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<script type="text/javascript" src="/admin/lib/jquery-1.6.1.min.js"></script>
		<script type="text/javascript" src="/admin/js/page.js"></script>
		<link rel="stylesheet" type="text/css" href="/admin/style.css" />
		<link rel="stylesheet" type="text/css" href="/admin/forms.css" />
	</head>
	<body>
		<div id="header">
			<a href="/admin"><img src="/admin/images/logo.png" /></a>
			<span class="domain">{$domainname}</span>
		</div>

		<h1>{$path->getTitle('top')}</h1>

		<div id="leftmenu">
			{include file='menu.tpl'}
		</div>

		<div id="content">
			{include file='message.tpl'}
			{if $template}
				{include file=$template}
			{/if}
		</div>

	</body>
</html>
