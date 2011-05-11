<!DOCTYPE html>
<html>
	<head>
		<title>{$path->getTitle('condensed', ' &raquo; ', 'ltr')}</title>
		<meta charset="utf-8" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<link rel="stylesheet" type="text/css" href="/admin/style.css" />
	</head>
	<body>
		<div id="header">
			<a href="/admin"><img src="/admin/images/header.png" /></a>
			<span class="domain">{$domainname}</span>
		</div>

		<div id="content">
			<h1>{$path->getTitle('top')}</h1>
			{include file='message.tpl'}
			{if $template}
				{include file=$template}
			{/if}
		</div>

		<div id="leftmenu">
			{include file='menu.tpl'}
		</div>

	</body>
</html>
