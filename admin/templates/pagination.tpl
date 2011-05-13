{if $pageCount > 1}
<div class="pagination">

	{if $previous}
		<a href="{url page=$previous}">&laquo; {t}paginator_prev{/t}</a>
	{else}
		<span class="disabled">&laquo; {t}paginator_prev{/t}</span>
	{/if}

	{foreach $pagesInRange as $page}
		{if $page == $current}
			<span class="current">{$page}</span>
		{else}
			<a href="{url page=$page}">{$page}</a>
		{/if}
	{/foreach}

	{if $next}
		<a href="{url page=$next}">{t}paginator_next{/t} &raquo;</a>
	{else}
		<span class="disabled">{t}paginator_next{/t} &raquo;</span>
	{/if}

</div>
{/if}
