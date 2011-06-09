<h1>{t}media_chooser_insert{/t}</h1>

<h2>{$picture->title}</h2>
{if $picture->size || $picture->mimetype|substr:0:5 == 'image'}
	<a target="_blank" href="{$picture->pub}"><img src="{$picture->thumbnail.medium.pub}" alt="{$picture->alt}" title="{$picture->title}" /></a>
	<div><strong>{t}file_original_size{/t}:</strong> {$picture->size}</div>
{/if}
<div><strong>{t}file_mimetype{/t}:</strong> {$picture->mimetype}</div>
<div><strong>{t}file_upload_url{/t}:</strong> <a href="{$picture->pub}">http://{$smarty.server.SERVER_NAME}{$picture->pub}</a></div>

<label for="size">{t}media_chooser_size{/t}</label> 
<select name="size">
	<option value="">{$picture->size} ({t}file_original_size{/t})</option>
	{foreach $picture->thumbnail as $key => $osize}
		<option value="{$key}">{$osize.size}</option>
	{/foreach}
	<option value="custom">{t}media_chooser_size_custom{/t}</option>
</select>
