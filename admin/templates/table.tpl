<table>
	<tr>
		{foreach $config.params.fields as $field}
			<th>{$field}</th>
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
