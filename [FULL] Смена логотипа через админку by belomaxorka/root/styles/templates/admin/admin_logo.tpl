<h1>{L_LOGO_MANAGEMENT}</h1>

<p>{L_LOGO_EXPLAIN}</p>
<br />

<form action="{S_LOGO_ACTION}" method="post" enctype="multipart/form-data">
{S_HIDDEN_FIELDS}

<table class="forumline">
<col class="row1">
<col class="row2">
<col class="row1">
<tr>
	<th colspan="3">{L_CURRENT_LOGO}</th>
</tr>
<tr>
	<td colspan="3" class="tCenter">
		<img src="{CURRENT_LOGO_PREVIEW}" alt="Current Logo" style="max-width: 300px; max-height: 100px;">
	</td>
</tr>
<tr>
	<th colspan="3">{L_UPLOAD_NEW_LOGO}</th>
</tr>
<tr>
	<td><h4>{L_SELECT_FILE}</h4><h6>{L_ALLOWED_FORMATS}</h6></td>
	<td colspan="2"><input type="file" name="logo_upload" accept="image/*" /></td>
</tr>
<tr>
	<th colspan="3">{L_SELECT_EXISTING_LOGO}</th>
</tr>
<!-- BEGIN logos -->
<tr>
	<td>
		<label>
			<input type="radio" name="logo_select" value="{logos.LOGO_PATH}" {logos.SELECTED} />
			{logos.LOGO_NAME}
		</label>
	</td>
	<td class="tCenter">
		<img src="{logos.LOGO_PATH_PREVIEW}" alt="{logos.LOGO_NAME}" style="max-width: 200px; max-height: 80px;">
	</td>
	<td class="tCenter">
		<!-- IF not logos.IS_DEFAULT -->
		<button type="button" class="liteoption" onclick="deleteLogo('{logos.LOGO_PATH}', '{logos.LOGO_NAME}')">{L_DELETE}</button>
		<!-- ENDIF -->
	</td>
</tr>
<!-- END logos -->
<tr>
	<td class="catBottom" colspan="3">
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" class="liteoption" />
	</td>
</tr>
</table>

</form>

<!-- Delete form -->
<form id="deleteLogoForm" action="{S_LOGO_ACTION}" method="post" style="display: none;">
	<input type="hidden" name="logo_to_delete" id="logo_to_delete" value="" />
	<input type="hidden" name="delete_logo" value="1" />
	<input type="hidden" name="confirm_delete" value="1" />
</form>

<script>
function deleteLogo(logoPath, logoName) {
	if (confirm('{L_LOGO_DELETE_CONFIRM}'.replace('%s', logoName))) {
		document.getElementById('logo_to_delete').value = logoPath;
		document.getElementById('deleteLogoForm').submit();
	}
}
</script>

<br clear="all" />
