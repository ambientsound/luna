{*
 * Template: template_page_default
 *}
<h2>{$page->title}</h2>

<article>
	{if $page->loadPicture()}
		<div class="page-picture alignleft">
			<img src="{$page->picture->thumbnail.medium.pub}" alt="{$page->picture->alt}" title="{$page->picture->title}" />
		</div>
	{/if}
	{$page->body}
</article>
