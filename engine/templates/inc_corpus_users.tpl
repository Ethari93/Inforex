<table class="tablesorter" cellspacing="1" id="corpus_update" style="width: 300px; margin: 10px">
	<thead>
		<tr>
			<th>User</th>
			<th>Assign</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$users_in_corpus item=user}
		<tr>
			<td>{$user.screename}</td>
			<td><input {if $user.role}checked="checked"{/if} class="userInCorpus" type="checkbox" element_type="users" value="{$user.user_id}" /></td>
		</tr>
		{/foreach}
	</tbody>
</table>