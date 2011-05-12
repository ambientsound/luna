<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		{foreach $config.fields as $field}
			<th>{t}{$config.prefix}{$field}{/t}</th>
		{/foreach}
	</tr>
	{foreach $table as $row}
		<tr class="{cycle values='white,gray'}">
			{foreach $row as $cell}
				{strip}
					<td>
					{if $cell->link}<a href="/admin/{$params.controller}/{$cell->link}/{$table->primary}/{$row->{$table->primary}}">{/if}
					{$cell}
					{if $cell->link}</a>{/if}
					</td>
				{/strip}
			{/foreach}
		</tr>
	{/foreach}
</table>
