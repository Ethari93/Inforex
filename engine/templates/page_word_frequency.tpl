{include file="inc_header.tpl"}

<h1>Word frequency list</h1>

<div style="float: left; width: 400px;">
	<h2>Common filters</h2>

    {capture name=link_ext_filters assign=link_ext_filters}{foreach from=$filters item=filter}{if $filter.selected}&amp;filter_{$filter.name}={$filter.selected}{/if}{/foreach}{/capture}
	
	<table class="tablesorter" cellspacing="1" style="width: 400px">
		<tr>
		    <th style="width: 100px">Parts of speech:</th>
		    <td>
	           {assign var=pos_set  value="0"}
		       {foreach from=$classes item=class}
	                {if $class==$ctag}
	                    {assign var=pos_set  value=$class}
	                    <em>{$class}</em>                    
	                {else}
	                    <a href="index.php?page={$page}&amp;corpus={$corpus.id}&amp;subcorpus={$subcorpus}&amp;ctag={$class}{$link_ext_filters}">{$class}</a>
	                {/if},
	            {/foreach}
	            {if $pos_set=="0"}
	                <em>wszystkie</em>
	            {else}
	                <a href="index.php?page={$page}&amp;corpus={$corpus.id}&amp;subcorpus={$subcorpus}{$link_ext_filters}">wszystkie</a>
	            {/if}                        
		    </td>    
		</tr>
	    <tr>
	        <th style="width: 100px">Subcorpora:</th>
	        <td>
	        {assign var=subcorpus_set  value=0}
	        {foreach from=$subcorpora item=s}
	            {if $s.subcorpus_id==$subcorpus} 
	                {assign var=subcorpus_set value=1}
	                <em>{$s.name}</em>
	            {else}
	                <a href="index.php?page={$page}&amp;corpus={$corpus.id}&amp;ctag={$ctag}&amp;subcorpus={$s.subcorpus_id}{$link_ext_filters}">{$s.name}</a>
	            {/if},                
	        {/foreach}
	        {if $subcorpus_set==0}
	            <em>wszystkie</em>
	        {else}
	            <a href="index.php?page={$page}&amp;corpus={$corpus.id}&amp;ctag={$ctag}{$link_ext_filters}">wszystkie</a>
	        {/if}        
	        </td>    
	    </tr>
	</table>
	
	{if $filters|@count>0}
	<h2>Custom filters</h2>
	
	<table class="tablesorter" cellspacing="1">
	
	    {foreach from=$filters item=filter}
	    <tr>
	        <th style="width: 100px">{$filter.name}</th>
	        <td>
	        {assign var=filter_set  value=0}
	        {foreach from=$filter.values item=value}
	            {if $value==$filter.selected}
	                {assign var=filter_set value=1}
	                <em>{$value}</em>            
	            {else}
	            <a href="index.php?page={$page}&amp;corpus={$corpus.id}&amp;ctag={$ctag}&amp;subcorpus={$subcorpus}&amp;filter_{$filter.name}={$value}">{$value}</a>
	            {/if},
	        {/foreach}
	        {if $filter_set==0}
	            <em>wszystkie</em>
	        {else}
	            <a href="index.php?page={$page}&amp;corpus={$corpus.id}&amp;ctag={$ctag}&amp;subcorpus={$subcorpus}">wszystkie</a>
	        {/if}        
	        </td>
	    </tr>
	    {/foreach}
	</table>
	{/if}
</div>

<div style="margin-left: 420px">
    <h2>List of words</h2>

    <table id="words_frequences" class="tablesorter" cellspacing="1" style="width: 200px">
    <thead>
        <tr>
            <th>No.</th>
            <th>Word</th>
            <th>Count</th>
            <th>Documents</th>
            <th title="% of documents containing the word">Doc.&nbsp;%</th>
            <th title="proportion of documents to word count">Doc./Count</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$words item=word name=word}
        <tr>
            <td style="text-align: right">{$smarty.foreach.word.index+1}</td>
            <td><b>{$word.base|escape:"html"}</b></td>
            <td style="text-align: right">{$word.c}</td>
            <td style="text-align: right"><a href="index.php?page=browse&amp;corpus={$corpus.id}&amp;reset=1&amp;base={$word.base|escape:"html"}&amp;subcorpus={$subcorpus}" title="show list of documents in new window">{$word.docs}</a></td>
            <td style="text-align: right">{$word.docs_per|string_format:"%.2f"}%</td>
            <td style="text-align: right">{$word.docs_c|string_format:"%.4f"}</td>
        </tr>    
        {/foreach}
    </tbody>
    </table>
    {if $words|@count==0}
    <div style="padding: 10px">
    <i>There not words for these criteria</i>
    </div>
    {/if}
</div>

<br style="clear: both"/>

{include file="inc_footer.tpl"}