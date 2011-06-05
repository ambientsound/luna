<script type="text/javascript" src="/admin/include/js/page.js"></script>

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
		{$form->nodetype}
		{$form->template}
		{$form->metadesc}
		{if $object->stickers}
			<div id="stickers">
			{foreach $object->stickers as $key => $value}
				{$key}={$value}<br />
			{/foreach}
			</div>
		{/if}
	</div>
	<div class="left">
		{$form->title}
		<div class="selector">
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
		</div>
		{include file="page/type/`$form->getValue('nodetype')`.tpl"}
	</div>
</form>
