{*
 * Template: media_chooser_template_default
 *}
{strip}
{if $opts.link}<a href="{if $opts.link == 'big'}{$picture->pub}{elseif $opts.link == 'custom'}{$opts.customlink}{/if}">{/if}
<img {if $opts.align}class="{$opts.align}"{/if} src="{if $opts.size}{$picture->thumbnail.{$opts.size}.pub}{else}{$picture->pub}{/if}" title="{$picture->title}" alt="{if $picture->alt}{$picture->alt}{else}{$picture->title}{/if}" />
{if $opts.link}</a>{/if}
{/strip}
