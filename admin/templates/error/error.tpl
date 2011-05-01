<h1>{$title.top}</h1>

{if $exception}

	<h2>Exception information:</h2>
	<p>
		<b>Message:</b> {$exception->getMessage()}
	</p>

	<h3>Stack trace:</h3>
	<pre>{$exception->getTraceAsString()}
	</pre>

	<h3>Request Parameters:</h3>
	<pre>{var_export($request->getParams(), true)}
	</pre>

{else}
	
	<p>
		{$errordescription}
	</p>

{/if}
