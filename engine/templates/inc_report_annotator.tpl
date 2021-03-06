{*
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 *}
 
<div id="dialog" title="Błąd" style="display: none;">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
		<span class="message"></span>
	</p>
	<p><i><a href="">Refresh page.</a></i></p>
</div>

<div class="col-main {if $flags_active}col-md-7{else}col-md-8{/if} scrollingWrapper" id="col-main">
	<div class="panel panel-primary">
		<div class="panel-heading">Document content</div>
		<div id="widget_text" class="panel-body column" style="padding: 0">
			<div id="content">
				<div id="leftContent" style="float:left; width: {if $showRight}50%; border-right: 1px solid #E0CFC2{else}100%;{/if}" class="annotations scrolling content">
				  <div style="margin: 5px" class="contentBox {$report.format}">{$content|format_annotations}</div>
				</div>{*
				<div id="rightContent" style="{if !$showRight}display: none{/if};" class="annotations scrolling content rightPanel">
					  <div style="margin: 5px" class="contentBox {$report.format}">{$content_inline2|format_annotations}</div>
				</div>*}
				<div style="clear:both"></div>
			</div>
		</div>
	</div>
</div>

<div id="columnAnnotation" class="col-md-4 scrollingWrapper" style="display: none;">
	<div class="">
		<div id="annotationLoading" style="display: none;">
			Loading data ... <img src="gfx/ajax.gif" />
		</div>


		<div id="annotationEditor">
			<div id="annotation-details" class="panel panel-primary">
				<div class="panel-heading">
					<a href="#" class="btn btn-xs btn-primary annotation_redo" style="float: right" title="Close annotation editor"><i class="fa fa-window-close" aria-hidden="true"></i></a>
					Annotation details
				</div>
				<div class="panel-body" style="padding: 0">
					<table style="font-size: 8pt" class="table table-striped" cellspacing="1">
						<tr>
							<th>Id:</th>
							<td class="value" id="annotation_id">-</td>
						</tr>
						<tr>
							<th>Text:</th>
							<td class="value" id="annotation_text">-</td>
						</tr>
						<tr>
							<th>Type:</th>
							<td style="vertical-align: top">
								<span id="annotation_redo_type" class="value" annotation-type-id=""></span>
								<input type="hidden" id="annotation_redo_type_id"/>
                                <div style="float:right; {if $annotation_mode == 'relation_agreement'}display: none;{/if}">&nbsp;&nbsp;<a href="#" id="changeAnnotationType" data-toggle="popover" title="change type"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></div>
                            </td>
						</tr>
						<tr>
							<th>Lemma:</th>
							<td style="vertical-align: middle">
								<input type="text" id="annotation_lemma" class="form-control" value=""/>
							</td>
						</tr>
					</table>
				</div>
				<div class="panel-footer">
					<input type="button" value="Save and close" class="btn btn-sm btn-primary" id="annotation_save" disabled="true"/>
                    {if $annotation_mode != 'relation_agreement'}
						<a href="#" type="button" style="float: right" id="annotation_delete" class="btn btn-sm btn-danger" title="Delete annotation"><i class="fa fa-trash" aria-hidden="true"></i></a>
                    {/if}
				</div>
			</div>

			<div id="annotation-relations" class="panel panel-default">
				<div class="panel-heading">Annotation relations</div>
				<div class="panel-body" style="padding: 0">
					<div class="annotations relationsContainer scrolling">
						<table class="table table-striped relations" cellspacing="1" style="font-size: 8pt">
							<thead>
								<tr>
									<th>Id</th>
									<th>Relation type</th>
									<th>Target annotation</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
				<div class="panel-footer">
					<input type="button" value="Cancel" class="btn btn-sm btn-warning relation-cancel" style="display: none"/>
					<div class="dropup relation-types">
						<button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Add relation
							<span class="caret"></span></button>
						<ul class="dropdown-menu"></ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<div id="col-config" class="col-md-4 scrollingWrapper">
	<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
		{include file="inc_report_annotator_configuration.tpl" show=true}
        {include file="inc_report_annotator_annotation_pad.tpl"}
		{include file="inc_report_annotator_annotations.tpl"}
		{include file="inc_report_annotator_relations.tpl"}
	</div>
</div>