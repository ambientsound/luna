<ul>
{foreach from=$menu item=i}
	<li>
		<a {if $i.active} class="active" {/if} href="{$i.url}"><img src="/admin/images/menu/{$i.controller}.png" alt="" /><span>{$i.title}</span></a></li>
		{if $i.children}
			{include file='menu.tpl' menu=$i.children}
		{/if}
	</li>
{/foreach}
</ul>
