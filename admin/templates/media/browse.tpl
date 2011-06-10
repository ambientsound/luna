<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>{$path->getTitle('condensed', ' &raquo; ', 'ltr')}</title>
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<script type="text/javascript" src="/admin/include/lib/jquery-1.6.1.min.js"></script>
		<script type="text/javascript" src="/admin/include/lib/jquery-ui-1.8.13.custom.min.js"></script>
		<script type="text/javascript" src="/admin/include/lib/tinymce/tiny_mce_popup.js"></script>
		<script type="text/javascript" src="/admin/include/lib/tinymce/jquery.tinymce.js"></script>
		<script type="text/javascript" src="/admin/include/js/mediabrowse.js"></script>
		<link rel="stylesheet" type="text/css" href="/admin/include/style.css" />
		<link rel="stylesheet" type="text/css" href="/admin/include/mediabrowse.css" />
	</head>
	<body>
		<div id="header">
			<a href="/admin"><img src="/admin/include/images/logo.png" /></a>
		</div>
		
		<div id="mediabrowse">

			<div id="folderselect">
				{$folders}
				{include file='media/browse/list.tpl'}
			</div>

			<div id="manager">
				{if $picture->id}
					{include file='media/browse/select.tpl'}
				{/if}
			</div>

			<div id="uploader"{if $picture->id} style="display: none" {/if}>
				<h1>{t}media_chooser_upload{/t}</h1>
				{$upform}
			</div>


		</div>
	</body>
</html>
