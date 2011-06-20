{foreach $menu as $item}
	<li{if $page->url == $item.url} class="active"{/if}>
		<a href="{$item.url}">{$item.title}</a>
		{if $item.children}
			<ul>
				{menu object=$item.children}
			</ul>
		{/if}
	</li>
{/foreach}
