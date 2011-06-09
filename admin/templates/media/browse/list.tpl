<ul>
{foreach $pictures as $pic}
	<li>
		<h2>{$pic.title}</h2>
		<img {if $pic.id == $picture->id} class="active" {/if} id="{$pic.id}" src="{$pic.thumbnail.medium.pub}" alt="{$pic.alt}" title="{$pic.title}" />
	</li>
{/foreach}
</ul>
