<ul>
{foreach from=$menu item=i}
	<li class="{$i->class}{if $i->active} active{/if}">
		<a href="{$i->url}"><span>{$i->title}</span></a></li>
		{if $i->children}
			{include file='menu.tpl' menu=$i->children}
		{/if}
	</li>
{/foreach}
</ul>
