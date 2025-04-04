<h1>{L_PRIVATE_MESSAGING}</h1>
<br/>

<table class="forumline">
	<tr>
		<th>{L_FROM}</th>
		<th>{L_TO}</th>
		<th>{L_POST_TIME}</th>
		<th>{L_PRIVATE_MESSAGE}</th>
		<th>{L_IP_ADDRESS}</th>
	</tr>
	<!-- BEGIN pmrow -->
	<tr class="{pmrow.ROW_CLASS}">
		<td class="tCenter">{pmrow.FROM}</td>
		<td class="tCenter">{pmrow.TO}</td>
		<td class="tCenter">{pmrow.DATE}</td>
		<td width="60%">{pmrow.MESSAGE}</td>
		<td class="tCenter"><a href="{$bb_cfg['whois_info']}{pmrow.IP}" class="gen" target="_blank">{pmrow.IP}</a></td>
	</tr>
	<!-- END pmrow -->
</table>
<br/>
{PAGINATION}
