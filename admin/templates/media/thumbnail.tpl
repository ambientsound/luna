{if $form}
	<h2>{t}thumbnail_create{/t}</h2>
	{$form}
{/if}

<h2>{t}thumbnail_existing{/t}</h2>
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<th>{t}thumbnails_t_size{/t}</th>
		<th>{t}thumbnails_t_slug{/t}</th>
		<th>{t}thumbnails_t_description{/t}</th>
		<th>{t}thumbnails_t_actions{/t}</th>
		<th>{t}thumbnails_t_preview{/t}</th>
	</tr>
	{foreach $globalsizes as $size}
		<tr>
			<td>{$size.size}</td>
			<td>{$size.slug}</td>
			<td>{$size.description}</td>
			<td>{if !$size.permanent && $acl->can('model-files', 'delete-thumbnail')}<a href="/admin/media/thumbnail?delete={$size.size}">{t}thumbnails_t_action_delete{/t}</a>{/if}</td>
			<td><img src="{$picture->thumbnail.{$size.slug}.pub}" alt="{$picture->alt}" title="{$picture->title}" /></td>
		</tr>
	{/foreach}
</table>
