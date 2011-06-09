<script type="text/javascript" src="/admin/include/js/menu.js"></script>

{$form->prepareRender()}
<form id="form_menus" method="{$form->getMethod()}" action="{$form->getAction()}">

	{$form->id}
	{$form->title}
	{$form->add_link_group}
	{$form->mode}
	{$form->structure}
	{$form->page_id}

	<ul id="menu-items">
		{if $object->loadChildren()}
			{foreach $object->children as $item}
				<li>
					<input type="hidden" name="menuitem[]" value="{json_encode($item)|htmlentities}" />
					<div>
						<span class="title">{$item.title}</span>
						<span class="url">{$item.url}</span>
					</div>
				</li>
			{/foreach}
		{/if}
	</ul>

	{$form->submit}

</form>
