{*
 * Template: template_page_default
 *}
<h2>{$node->title}</h2>

{if $node->kicker}
	<p class="kicker">{$node->kicker}</p>
{/if}

{if $node->lead}
	<p class="lead">{$node->lead}</p>
{/if}

<article>
	{$node->body}
</article>
