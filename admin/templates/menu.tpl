<ul>
{foreach from=$menu item=i}
	<li>
		<a {if $request.controller == $i} class="active" {/if} href="/admin/{$i}"><img src="/admin/g/{$i}.png" alt="" /><span>{t}cms_menu_{$i}{/t}</span></a></li>
		{if $menu.children}
			{include file='menu.tpl' menu=$i.children}
		{/if}
	</li>
{/foreach}
</ul>
