<div class="right">
	<h2>{$object->title}</h2>
	{if $object->size || $object->mimetype|substr:0:5 == 'image'}
		<a target="_blank" href="{$object->pub}"><img src="{$object->thumbnail.medium.pub}" alt="{$object->alt}" title="{$object->title}" /></a>
		<div><strong>{t}file_original_size{/t}:</strong> {$object->size}</div>
		<div><strong>{t}file_thumbnail_sizes{/t}:</strong> 
			{$index=1}
			{foreach $object->thumbnail as $key => $osize}
				<a href="{$osize.pub}">{$osize.size}</a>{if $index++ < $object->thumbnail|@count}, {/if}
			{/foreach}
		</div>
	{/if}
	<div><strong>{t}file_mimetype{/t}:</strong> {$object->mimetype}</div>
	<div><strong>{t}file_upload_url{/t}:</strong> <a href="{$object->pub}">http://{$smarty.server.SERVER_NAME}{$object->pub}</a></div>
</div>
<div class="left">
	{$form}
</div>
