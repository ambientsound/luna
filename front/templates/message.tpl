{foreach $messages as $m}
	<div class="message">
		{$m}
	</div>
{/foreach}
{foreach $errors as $e}
	<div class="error">
		<strong>{t}error{/t}:</strong> {$e}
	</div>
{/foreach}
