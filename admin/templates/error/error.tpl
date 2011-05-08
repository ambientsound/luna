{if $exception}
	<div class="errordump">
		<h2>Error message:</h2>
		<p><strong>{$exception->getMessage()}</strong></p>

		<h2>Stack trace:</h2>
		<div class="stacktrace">
			{foreach $stacktrace as $line}
				{$line}<br />
			{/foreach}
		</div>

		<h2>Request Parameters:</h2>
		<pre>{var_export($params, true)}
		</pre>
	</div>
{else}
	<p>
		{$errordescription}
	</p>
{/if}
