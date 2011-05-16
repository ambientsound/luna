{$form->prepareRender()}
<form id="form_page" method="{$form->getMethod()}" action="{$form->getAction()}">
	{$form->id}
	<div class="right">
		{$form->modified}
		{$form->submit}
		{$form->template}
		{$form->metadesc}
	</div>
	<div class="left">
		{$form->title}
		<div class="url-selector">
			{$form->slug->renderLabel()}
			{strip}
				<span class="server">http://{$smarty.server.SERVER_NAME}</span>
				<span class="parent">{$form->parent->getMultiOption($form->parent->getValue())}/</span>
				<span class="slug">{$form->slug->renderViewHelper()}</span>
			{/strip}
		</div>
		<div class="parent-selector">
			{$form->parent}
		</div>
		{$form->body}
		{$form->editorial}
	</div>
</form>
