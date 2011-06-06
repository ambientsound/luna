<div class="picture-chooser">
	<div class="picture-drop" {if $form->getValue('use_folder')}style="display: none"{/if}>
		<h2>{t}form_pages_heading_pictures_connected{/t}</h2>
		<div>
			<ul>
				{if $object->loadRealGalleryImages()}
					{foreach $object->pictures as $picture}
						<li><img id="{$picture.id}" src="{$picture.thumbnail.small.pub}" title="{$picture.title}" alt="{$picture.alt}" /></li>
						{$ids[]=$picture.id}
					{/foreach}
				{/if}
			</ul>
			<br style="clear:left" />
			{if $ids}{$ids=join(',', $ids)}{/if}
			{$form->pictures->setValue($ids)}
		</div>
	</div>
	<div class="picture-source">
		<h2>{t}form_pages_heading_pictures_add{/t}</h2>
		{$form->folder_id}
		{$form->use_folder}
		<ul>
			{gallery folder_id=$form->getValue('folder_id') limit=0 assign='gallery'}
			{foreach $gallery as $picture}
				<li><img id="{$picture.id}" src="{$picture.thumbnail.small.pub}" title="{$picture.title}" alt="{$picture.alt}" /></li>
			{/foreach}
		</ul>
	</div>
</div>
