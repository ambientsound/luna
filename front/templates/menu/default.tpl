{foreach $menu as $item}
	<li>
		<a href="{$item.url}">{$item.title}</a>
		{if $item.children}
			<ul>
				{menu object=$item.children}
			</ul>
		{/if}
	</li>
{/foreach}
