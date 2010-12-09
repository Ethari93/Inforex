{if $filter_type == "text"}
	<div class="filter_box">
		{if $search}
			<a class="cancel" href="index.php?page=browse&amp;corpus={$corpus.id}&amp;search="><small class="toggle">anuluj</small>
		{else}
			<a class="toggle" label="#filter_search" href=""><small class="toggle">pokaż/ukryj</small>
		{/if}
			<h2 {if $search}class="active"{/if}>Szukaj <small>w tytule/treści</small></h2>
		</a>
		<div id="filter_search" class="options" {if !$search}style="display: none"{/if}>
			<form action="index.php?page=browse">
				<input type="hidden" name="corpus" value="{$corpus.id}"/>
				<input type="checkbox" name="search_field[]" value="title" style="vertical-align: middle" {if $search_field_title}checked="checked"{/if}> w tytule,
				<input type="checkbox" name="search_field[]" value="content" style="vertical-align: middle" {if $search_field_content}checked="checked"{/if}> w treści<br/>				
				<input type="text" name="search" value="{$search}" style="width: 150px"/>
				<input type="hidden" name="page" value="browse"/> 
				<input type="submit" value="szukaj"/>
			</form>
		</div>
	</div>
{/if} 

{if $filter_type == "status"}
	{assign var="attribute_options" value=$statuses}
	{include file="inc_filter_attribute.tpl"}
{/if}
	
{if $filter_type == "type"}
	{assign var="attribute_options" value=$types}
	{include file="inc_filter_attribute.tpl"}
{/if}

{if $filter_type == "year"}
	{assign var="attribute_options" value=$years}
	{include file="inc_filter_attribute.tpl"}
{/if}

{if $filter_type == "annotation"}
	{assign var="attribute_options" value=$annotations}
	{include file="inc_filter_attribute.tpl"}
{/if}
