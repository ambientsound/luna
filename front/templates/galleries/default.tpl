{*
 * Template: template_galleries_default
 *}
<h2>{$page->title}</h2>

<article>
	{$page->body}
	{if $page->loadImages()}
		{if $params.picture}
			{$picture=$page->pictures->getItem($params.picture)}
			<h2>&laquo;{$picture.title}&raquo;</h2>
			<img src="{$picture.pub}" alt="{$picture.alt}" title="{$picture.title}" />
			{if $picture.alt}
				<p>
					{$picture.alt}
				</p>
			{/if}
		{else}
			{foreach $page->pictures as $index => $picture}
				<a href="{$page->url}?picture={$index+1}"><img src="{$picture.thumbnail.medium.pub}" alt="{$picture.alt}" title="{$picture.title}" /></a>
			{/foreach}
		{/if}
	{/if}
</article>
