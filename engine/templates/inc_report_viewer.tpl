{*
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 *}


<div id="col-agreement" class="col-md-11 scrollingWrapper">
	<div class="panel panel-primary">
		<div class="panel-heading">Document content</div>
		<div class="panel-body" style="padding: 0">
			{if $exceptions|@count > 0}

			<div class="infobox-light">The document could not be displayed due to structure errors.</div>

			{else}

			<table style="width: 100%; margin-top: 5px;" class="scrolling-pane">
				<tr>
					<td style="vertical-align: top">
						<div class="column" id="widget_text">
							<div id="edit_content" class="scrollingAccordion">
								<div id="leftContent" style="float:left; width: 50%; border-right: 1px solid #E0CFC2" class="annotations scrolling content">
									  <div style="margin: 5px" class="contentBox">{$content_html|format_annotations}</div>
								</div>

								<div id="rightContent" class="annotations scrolling content rightPanel">
									<textarea name="content" id="report_content">{$content_source|escape}</textarea>
								</div>
								<div style="clear:both"></div>
							</div>
						</div>
					</td>
				</tr>
			</table>

			{/if}
		</div>
	</div>
</div>