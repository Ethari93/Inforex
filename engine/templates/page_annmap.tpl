{include file="inc_header.tpl"}

<h1>Annotations statistics</h1>

<div style="float: left; width: 400px;">
	<h2>Common filters</h2>

    {capture name=link_ext_filters assign=link_ext_filters}{foreach from=$filters item=filter}{if $filter.selected}&amp;filter_{$filter.name}={$filter.selected}{/if}{/foreach}{/capture}

	<table class="tablesorter" cellspacing="1">
	    <tr>
	        <th style="width: 100px">Subcorpus:</th>
	        <td>
	        {assign var=subcorpus_set  value=0}
	        {foreach from=$subcorpora item=s}
	            {if $s.subcorpus_id==$subcorpus} 
	                {assign var=subcorpus_set value=1}
	                <em>{$s.name}</em>
	            {else}
	                <a href="index.php?page=annmap&amp;corpus={$corpus.id}&amp;subcorpus={$s.subcorpus_id}">{$s.name}</a>
	            {/if},                
	        {/foreach}
	        {if $subcorpus_set==0}
	            <em>all</em>
	        {else}
	            <a href="index.php?page=annmap&amp;corpus={$corpus.id}">all</a>
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
	<h2>Number of annotations according to categories and subcategories <small>(click the row to expand/roll-back)</h2>

	<table cellspacing="1" class="tablesorter" id="annmap" style="width: 800px">
		<thead>
		
		<tr>
			<th colspan="3">Annotation</th>		
			<th colspan="3">Count</th>
		</tr>
		
		<tr>
			<th rowspan="2" style="width: 150px">Group</th>
			<th rowspan="2" style="width: 150px">Subgroup</th>
			<th rowspan="2">Category/Value</th>
            <th style="text-align: right; width: 60px">docs</th>
			<th style="text-align: right; width: 60px">unique</th>
			<th style="text-align: right; width: 60px">all</th>
		</tr>
		</thead>
		<tbody>
		{foreach from=$sets key=setName item=set}
			<tr class="setGroup expandable">
				<td colspan="4">{$setName}</td>
				<td style="text-align:right">{$set.meta.unique}</td>
				<td style="text-align:right">{$set.meta.count}</td>
			</tr>
			{foreach from=$set.rows key=subsetName item=subset}
				<tr class="subsetGroup expandable"  style="display:none">
					<td class="empty"></td>
					<td colspan="3">{$subsetName}</td>
					<td style="text-align:right">{$subset.meta.unique}</td>
					<td style="text-align:right">{$subset.meta.count}</td>
				</tr>
				
				{foreach from=$subset.rows key=typeName item=tag}
					{if isset($tag) and is_array($tag)}
						<tr class="annotation_type" style="display:none">
							<td colspan="2" class="empty"></td>
							<td>
							     {if $tag.count > 0}
							         <a href="." class="toggle_simple" label=".annotation_type_{$tag.type}"><b>{$tag.type}</b></a>
							     {else}
							         <span style="color: grey">{$tag.type}</span>
							     {/if}
							</td>
                            <td style="text-align:right">{$tag.docs}</td>
							<td style="text-align:right">{$tag.unique}</td>
							<td style="text-align:right">{$tag.count}</td>
						</tr>
						<tr class="annotation_type_{$tag.type} annotation_type_names expandable" style="display: none">
							<td colspan="2" class="empty2"></td>
							<td colspan="4"> 
								<ol>
								{foreach from=$tag.details item=detail}
									<li class="annotation_item">
										<span style="float: right;">{$detail.count}</span>
										<span style="margin-right: 50px">{$detail.text}</span>
										<div class="annotationItemLinks"></div>
									</li>
								{/foreach}
								</ol>
							</td>
						</tr>
					{/if}
				{/foreach}
			{/foreach}
		{/foreach}
		</tbody>
	</table>	
</div>	

<br style="clear: both;"/>

{include file="inc_footer.tpl"}