<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		{foreach $config.fields as $field}
			{if $params.order == 'asc' && $params.sort == $field}{$order='desc'}{else}{$order='asc'}{/if}
			<th><a class="{if $params.sort == $field}active{/if}" href="{url sort=$field order=$order}">{t}{$config.prefix}{$field}{/t}</a>
			{if $params.sort == $field}{if $params.order == 'desc'}&darr;{else}&uarr;{/if}{/if}
			</th>
		{/foreach}
	</tr>
	{foreach $table as $row}
		<tr class="{cycle values='white,gray'}">
			{foreach $row as $cell}
				{strip}
					<td>
					{if $cell->actions}
						{foreach $cell->actions as $action}
							<a class="{$action}" href="/admin/{$config.controller}/{$action}/{$config.primary}/{$row->{$config.primary}}">{t}{$config.prefix}action_{$action}{/t}</a> &nbsp;
						{/foreach}
					{/if}
					{if $cell->link}<a href="/admin/{$config.controller}/{$cell->link}/{$config.primary}/{$row->{$config.primary}}">{/if}
					{$cell}
					{if $cell->link}</a>{/if}
					</td>
				{/strip}
			{/foreach}
		</tr>
	{/foreach}
</table>

{$table->getPaginator()}
