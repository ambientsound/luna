<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		{foreach $config.fields as $field}
			<th>{t}{$config.prefix}{$field}{/t}</th>
		{/foreach}
	</tr>
	{foreach $table as $row}
		<tr>
			{foreach $row as $cell}
				<td>{$cell}</td>
			{/foreach}
		</tr>
	{/foreach}
</table>
