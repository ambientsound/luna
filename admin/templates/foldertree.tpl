{foreach $folders as $folder}
	<ul>
		<li>
			<a rel="{$folder.id}" href="javascript:;">{$folder.name}</a>
			{if $folder.children}
				{include file='foldertree.tpl' folders=$folder.children}
			{/if}
		</li>
	</ul>
{/foreach}
