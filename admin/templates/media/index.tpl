<script type="text/javascript" src="/admin/include/lib/jstree/jquery.jstree.js"></script>
<script type="text/javascript" src="/admin/include/js/media.js"></script>

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
			<li><a rel="0" href="javascript:;">{t}media_root_folder{/t}</a>
			{include file='foldertree.tpl'}
			</li>
		</ul>
	</div>
</div>

<div class="treeview-margin">
	<h2>{t}media_viewing_folder{/t} <span class="folder-title">{t}media_root_folder{/t}</span></h2>
	<div>
		{$table}
	</div>
</div>
