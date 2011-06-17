<script type="text/javascript">

	var uploadify_scriptdata = {
		{session_name()} : '{session_id()}'
	};

</script>
<script type="text/javascript" src="/admin/include/lib/uploadify/swfobject.js"></script>
<script type="text/javascript" src="/admin/include/lib/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/admin/include/lib/jstree/jquery.jstree.js"></script>
<script type="text/javascript" src="/admin/include/js/media.js"></script>

<link rel="stylesheet" href="/admin/include/lib/uploadify/uploadify.css" />

<div class="treeview-container">
	<h2>{t}media_select_folder{/t}</h2>
	<div class="full-checkbox-element">
		<label for="recurse">{t}form_folder_recurse{/t}</label>
		<div class="element checkbox-element">
			<input name="recurse" id="recurse" type="checkbox" checked="checked" />
		</div>
	</div>
	<div id="treeview">
		<ul>
			<li><a rel="0" id="rootnode" href="javascript:;">{t}media_root_folder{/t}</a>
			{include file='foldertree.tpl'}
			</li>
		</ul>
	</div>

	<button type="button" class="create-folder">{t}media_create_folder{/t}</button>
	<button type="button" class="delete-folder">{t}media_delete_folder{/t}</button>

	{$uploadform}

</div>

<div class="treeview-margin">
	<div>
		{$table}
	</div>
</div>
