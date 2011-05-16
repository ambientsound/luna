{foreach $messages as $m}
	<div class="message">
		{$m}
	</div>
{/foreach}
{foreach $errors as $e}
	<div class="error">
		{$e}
	</div>
{/foreach}
