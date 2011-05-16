{$form->prepareRender()}
<form id="form_page" method="{$form->getMethod()}" action="{$form->getAction()}">
	{$form->id}
	<div class="right">
		{$form->modified}
		{$form->submit}
		{if $form->modified->getUnfilteredValue()}
			<div class="preview"><a target="_blank" href="{strip}
				{$form->parent->getMultiOption($form->parent->getValue())}{if $form->parent->getValue() || $form->slug->getValue()}/{/if}
				{$form->slug->getValue()}
			{/strip}">&raquo; {t}page_goto_preview{/t}</a></div>
		{/if}
		{$form->template}
		{$form->metadesc}
	</div>
	<div class="left">
		{$form->title}
		<div class="url-selector">
			{$form->slug->renderLabel()}
			{strip}
				<span class="server">http://{$smarty.server.SERVER_NAME}</span>
				<span class="parent">{$form->parent->getMultiOption($form->parent->getValue())}{if $form->parent->getValue() || $form->slug->getValue()}/{/if}</span>
				<span class="slug">{$form->slug->renderViewHelper()}</span>
			{/strip}
			{$form->slug->renderErrors()}
		</div>
		<div class="parent-selector">
			{$form->parent}
		</div>
		{$form->body}
		{$form->editorial}
	</div>
</form>
