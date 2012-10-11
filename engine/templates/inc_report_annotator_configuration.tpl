{if $smarty.cookies.accordionActive=="cell_annotation_layers_header"}
<h3 id="cell_annotation_layers_header" class="ui-accordion-header ui-helper-reset ui-state-active ui-corner-top" aria-expanded="true" role="tab" tabindex="0">
	<span class="ui-icon ui-icon-triangle-1-s"></span>
	<a tabindex="-1" href="#">View configuration</a>
</h3>
<div style="vertical-align: top;padding-top: 12px; padding-bottom: 12px;display:block" class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content-active" role="tabpanel">
{else}
<h3 id="cell_annotation_layers_header" class="ui-accordion-header ui-helper-reset ui-state-default ui-corner-all" aria-expanded="false" role="tab" tabindex="-1">
	<span class="ui-icon ui-icon-triangle-1-e"></span>
	<a tabindex="-1" href="#">View configuration</a>
</h3>
<div style="vertical-align: top; padding: 5px; display:none" class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content-active" role="tabpanel">
{/if}
	<div id="annotation_layers" class="scrolling">
		<div style="padding: 5px; overflow-y:auto" class="">
		<table class="tablesorter" cellspacing="1">
			<thead>
			<tr>
		    	<th colspan="5" style="text-align: center">Annotation layers</th>
		    </tr>
			<tr>
				<th rowspan="2">Layer</th>
				<th colspan="3" style="text-align:center" title="Physically show/hide layer -- reload page is required to rebuild document structure" >Display</th>
				<th rowspan="2" style="text-align:center" title="Dynamically show/hide layer" >Show</th>
			</tr>
            <tr>
                <th style="text-align:center" title="Left" >Left</th>
                <th style="text-align:center" title="Right" >Right                              	
                	<input id="showRight" type="checkbox"{if $smarty.cookies.showRight=="true"} checked="checked"{/if} style="vertical-align: middle" title="Show/hide right panel"/>
                </th>
                <th style="text-align:center" title="None" >None</th>
            </tr>
			</thead>
			<tbody>
		    {foreach from=$annotation_types item=set key=k name=groups}
			    <tr class="layerRow hiddenRow" setid="{$set.groupid}">
			    	<td style="vertical-align: middle;font-weight:bold" class="layersList"><span class="toggleLayer ui-icon ui-icon-circlesmall-plus" style="float:left"></span><span class="layerName" style="clear:both"><a href="#">{$k}</a></span></td>
			    	<td style="vertical-align: middle;text-align:center"><input name="layerId{$set.groupid}" type="radio" class="leftLayer" /> </td>
			    	<td style="vertical-align: middle;text-align:center" class="rightPanel"><input name="layerId{$set.groupid}" type="radio" class="rightLayer" /> </td>
			    	<td style="vertical-align: middle;text-align:center; background-color: #FF9B9B"><input name="layerId{$set.groupid}" type="radio" class="clearLayer"/></td>
			    	<td style="vertical-align: middle;text-align:center"><input name="layerId{$set.groupid}" type="checkbox" class="hideLayer" /> </td>
			    </tr>  
		    	{foreach from=$set item=subset key=k2}
			    	{if $k2!="groupid" && $k2!="none"}
			    	<tr class="sublayerRow" subsetid="{$subset.subsetid}" style="display:none">
				    	<td style="vertical-align: middle"><span class="ui-icon ui-icon-carat-1-sw" style="float:left"></span><span class="layerName" style="clear:both"><a href="#">{$k2}</a></span></td>
				    	<td style="vertical-align: middle;text-align:center"><input name="sublayerId{$subset.subsetid}" type="radio" checked="checked" class="leftSublayer" /> </td>
				    	<td style="vertical-align: middle;text-align:center" class="rightPanel"><input name="sublayerId{$subset.subsetid}" type="radio" class="rightSublayer" /> </td>
				    	<td style="vertical-align: middle;text-align:center; background-color: #FF9B9B"><input name="sublayerId{$subset.subsetid}" type="radio" class="clearSublayer"/></td>
				    	<td style="vertical-align: middle;text-align:center"><input name="sublayerId{$subset.subsetid}" type="checkbox" class="hideSublayer" /> </td>
			    	</tr>
			    	{/if}
		    	{/foreach}
		    {/foreach}
		    <tr><th colspan="5"></th></tr>
		    <tr>
		    	<th colspan="5" style="text-align: center">Relation sets</th>
		    </tr>
		    {foreach from=$relation_sets item=rel_set}
		    	<tr>
		    		<td style="text-align: center"><input class="relation_sets" type="checkbox" value="{$rel_set.relation_set_id}" {if $rel_set.active}checked="checked"{/if} /></td>
		    		<td colspan="4" style="text-align: center"><span class="layerName" style="clear:both">{$rel_set.name}</span></td>
		    	</tr>
		    {/foreach}
		    
		    </tbody>
		    <tfoot>
			    <tr><th colspan="5"></th></tr>
				<tr>
					<th></th>
			        <th colspan="3" style="text-align: center"><button id="applyLayer" style="margin: 1px; font-size: 0.9em">Apply</button></th>
			        <th></th>
			    </tr>
		    </tfoot>						    			    
    	</table>	
    	</div>
        <div>                       
            {if $smarty.cookies.splitSentences=="true"}
            <input id="splitSentences" type="checkbox" checked="checked" style="vertical-align: middle"/> 
            {else}
            <input id="splitSentences" type="checkbox"  style="vertical-align: middle"/> 
            {/if}
            Display every sentence separately
        </div>          
	</div>	
</div>