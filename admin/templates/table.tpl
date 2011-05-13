<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		{foreach $config.fields as $field}
			{if $params.order == 'asc'}{$order='desc'}{else}{$order='asc'}{/if}
			<th><a class="{if $params.sort == $field}active{/if}" href="{url sort=$field order=$order}">{t}{$config.prefix}{$field}{/t}</a>
			{if $params.sort == $field} {if $params.order == 'desc'}&uarr;{else}&darr;{/if}{/if}
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
							<a href="/admin/{$params.controller}/{$action}/{$table->primary}/{$row->{$table->primary}}">{t}{$config.prefix}action_{$action}{/t}</a> &nbsp;
						{/foreach}
					{/if}
					{if $cell->link}<a href="/admin/{$params.controller}/{$cell->link}/{$table->primary}/{$row->{$table->primary}}">{/if}
					{$cell}
					{if $cell->link}</a>{/if}
					</td>
				{/strip}
			{/foreach}
		</tr>
	{/foreach}
</table>

{$table->getPaginator()}
