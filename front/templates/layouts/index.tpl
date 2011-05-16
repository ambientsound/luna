<!DOCTYPE html>
<html>
<head>
	<title>{$path->getTitle('condensed', ' &raquo; ', 'ltr')}</title>
	<meta name="robots" content="{if $options->main->searchable}index, follow{else}noindex, nofollow{/if}" />
	<meta charset="utf-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<link rel="stylesheet" type="text/css" href="/style.css" />
</head>
<body>
	<h1>Luna 3.0 test lab</h1>
	<p><a href="/admin">Control panel is this way</a>.</p>
	{if $path->count() > 1}
		<p>You are here:
			{foreach $path as $key => $crumb}
				<a href="{$crumb.url}">{$crumb.title}</a>
				{if $key+1 < $path->count()}&raquo;{/if}
			{/foreach}
		</p>
	{/if}
	<div id="content">
		{include file='message.tpl'}
		{if $template}
			{include file=$template}
		{/if}
	</div>
</body>
</html>
