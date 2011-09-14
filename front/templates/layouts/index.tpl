<!DOCTYPE html>
<html>
	<head>
		<title>{$path->getTitle('condensed', ' &raquo; ', 'ltr')}</title>
		{foreach $meta as $name => $content}
			<meta name="{$name}" content="{$content}" />
		{/foreach}
		<meta charset="utf-8" />
		<link rel="stylesheet" href="/include/style.css" />
	</head>
	<body>
		<h1>{$options->main->title}</h1>
		<nav>
			<ul id="mainmenu">
				{menu id=1}
				<li><a href="/admin">{t}luna_admin_login{/t}</a></li>
			</ul>
		</nav>
		{if $path->count() > 1 && !$frontpage}
			<nav>
				<div id="breadcrumbs">
					{foreach $path as $key => $crumb}
						<a href="{$crumb.url}">{$crumb.title}</a>
						{if $key+1 < $path->count()}&raquo;{/if}
					{/foreach}
				</div>
			</nav>
		{/if}
		<div id="content">
			{include file='message.tpl'}
			{if $template}
				{include file=$template}
			{/if}
		</div>
	</body>
</html>
