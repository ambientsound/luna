<h1>{t}media_chooser_insert{/t}</h1>

{if $picture->size || $picture->mimetype|substr:0:5 == 'image'}

{$insertform->prepareRender()}
<form id="form_mediabrowser" method="{$insertform->getMethod()}" action="{$insertform->getAction()}">
	<h2>{$picture->title}</h2>
	<div class="left">
		<a target="_blank" href="{$picture->pub}"><img rel="{$picture->id}" src="{$picture->thumbnail.medium.pub}" alt="{$picture->alt}" title="{$picture->title}" /></a>
		<div class="form-elements">
			{$insertform->id}
			{$insertform->template}
			{$insertform->submit}
		</div>
	</div>

	<div id="finfo">
		<div><strong>{t}file_original_size{/t}:</strong> {$picture->size}</div>
		<div><strong>{t}file_mimetype{/t}:</strong> {$picture->mimetype}</div>
		<div><strong>{t}file_upload_url{/t}:</strong> <a href="{$picture->pub}">http://{$smarty.server.SERVER_NAME}{$picture->pub}</a></div>
		{$insertform->submit}
		<div class="form-elements">
			{$insertform->size}
			{$insertform->customsize}
			{$insertform->align}
			{$insertform->link}
			{$insertform->customlink}
			{$insertform->title}
			{$insertform->alt}
		</div>
	</div>
</form>

{/if}
