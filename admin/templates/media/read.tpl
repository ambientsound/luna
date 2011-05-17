<div class="right">
	<h2>{$object->title}</h2>
	<a target="_blank" href="{$object->pub}"><img src="{$object->thumbnail.medium.pub}" alt="{$object->alt}" title="{$object->title}" /></a>
	<div><strong>{t}file_original_size{/t}:</strong> {$object->size}</div>
	<div><strong>{t}file_thumbnail_sizes{/t}:</strong> 
		{$osizes=array()}
		{foreach $object->thumbnail as $osize}
			{$osizes[]=$osize.size}
		{/foreach}
		{join(', ', $osizes)}
	</div>
</div>
<div class="left">
	{$form}
</div>
