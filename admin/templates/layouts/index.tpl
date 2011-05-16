<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>{$path->getTitle('condensed', ' &raquo; ', 'ltr')}</title>
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<script type="text/javascript" src="/admin/lib/jquery-1.6.1.min.js"></script>
		<script type="text/javascript" src="/admin/lib/tinymce/jquery.tinymce.js"></script>
		<script type="text/javascript" src="/admin/js/page.js"></script>
		<script type="text/javascript" src="/admin/js/tinymce.js"></script>
		<link rel="stylesheet" type="text/css" href="/admin/style.css" />
		<link rel="stylesheet" type="text/css" href="/admin/forms.css" />
	</head>
	<body>
		<div id="header">
			<a href="/admin"><img src="/admin/images/logo.png" /></a>
			<div class="meta">
				<span class="title">{$options->main->title}</span>
				<span>&nbsp; &middot; &nbsp;</span>
				<span class="domain"><a href="http://{$smarty.server.SERVER_NAME}">{$smarty.server.SERVER_NAME}</a></span>
				{if $user->isValid()}
					<span>&nbsp; &middot; &nbsp;</span>
					<span class="user">{t}topbar_welcome{/t} {$user->username}</span>
				{/if}
			</div>
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
