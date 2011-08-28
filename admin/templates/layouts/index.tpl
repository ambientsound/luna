<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>{$path->getTitle('condensed', ' &raquo; ', 'ltr')}</title>
		<meta name="robots" content="noindex, nofollow" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<script type="text/javascript" src="/admin/include/lib/jquery-1.6.1.min.js"></script>
		<script type="text/javascript" src="/admin/include/lib/ui/js/jquery-ui-1.8.13.custom.min.js"></script>
		<script type="text/javascript" src="/admin/include/lib/tinymce/jquery.tinymce.js"></script>
		<script type="text/javascript" src="/admin/include/js/tinymce.js"></script>
		<script type="text/javascript" src="/admin/include/js/luna.js"></script>
		<link rel="stylesheet" type="text/css" href="/admin/include/style.css" />
		<link rel="stylesheet" type="text/css" href="/admin/include/forms.css" />
		<link rel="stylesheet" type="text/css" href="/admin/include/lib/ui/css/custom-theme/jquery-ui-1.8.13.custom.css" />
		<link rel="stylesheet" type="text/css" href="/admin/style.css" />
	</head>
	<body>
		<div id="header">
			<a href="/admin"><img src="/admin/include/images/logo.png" /></a>
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
			{if $user->isValid()}
				{include file='menu.tpl'}
			{/if}
		</div>

		<div id="content">
			{include file='message.tpl'}
			{if $template}
				{include file=$template}
			{/if}
		</div>

	</body>
</html>
