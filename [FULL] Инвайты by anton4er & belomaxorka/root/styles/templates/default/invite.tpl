<h1 class="pagetitle">{L_INVITES}</h1>

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>
<br />

<!-- IF CAN_INVITE -->
<b style="color: #FF6666;">{L_DENNY_GET_INVITE_MSG}</b><br />
{L_DENNY_GET_INVITE_MSG_1}
<br />
{L_INVITE_YOU_CURRENT_RATIO}&nbsp;<b>{USER_RATING}</b>
<br />
{L_INVITE_TIME_REG_MOUNTH}&nbsp;<b>{USER_AGE}</b>

<br />
<div class="category">
<div class="cat_title">
<center>
{L_INVITE_CURRENT_RULES}
</center>
</div>
<table class="forumline">
	<tr>
		<th>&nbsp;{L_INVITE_MIN_RATIO}&nbsp;</th>
		<th>&nbsp;{L_INVITE_MIN_EXP}&nbsp;</th>
		<th>&nbsp;{L_INVITE_NUMBERS_IN_WEEK}&nbsp;</th>
		<th>&nbsp;{L_INVITE_ALLOWED_GROUP}&nbsp;</th>
	</tr>
<!-- BEGIN rule_row -->
	<tr>
		<td class="row1" align="center">{rule_row.USER_RATING}</td>
		<td class="row1" align="center">{rule_row.USER_AGE}</td>
		<td class="row2" align="center">{rule_row.INVITES_COUNT}</td>
		<td class="row2" align="center">{rule_row.USER_GROUP}</td>
	</tr>
<!-- END rule_row -->
</table>
</div>
<br />
<!-- ELSE -->
<form action="{U_INVITE}?mode=getinvite" method="post">
	<input type="submit" value="{L_GET_INVITE}">
</form>
<br />
{L_ALL_TIME_GETED_INVITE}&nbsp;<b>{INVITES_GETTED_ALL}</b><br />
{L_LAST_WEEK_GETED_INVITE}&nbsp;<b>{INVITES_GETTED_WEEK}</b><br />
{L_ALLOW_GET_INVITE}&nbsp;<b>{INVITES_MAY_GET}</b><br />
<br />
<!-- ENDIF -->
<!-- IF REFEREND -->
<div class="category">
<div class="cat_title">
<center>
{L_REFEREND_BY}
</center>
</div>
<table class="user_profile bordered w100" cellpadding="0" border="1">
<tr>
	<th class="thHead">{L_USER}</th>
	<th class="thHead">{L_INVITE_GET_DATE}</th>
	<th class="thHead">{L_INVITE_CODE}</th>
	<th class="thHead">{L_INVITE_ACTIVATION_DATE}</th>
</tr>
<!-- BEGIN referend_by -->
	<tr>
	  <td class="row1" align="center">{referend_by.REF_USER}</td>
	  <td class="row1" align="center">{referend_by.REF_GENERATION_DATE}</td>
	  <td class="row2" align="center">{referend_by.REF_INVITE_CODE}</td>
	  <td class="row1" align="center">{referend_by.REF_ACTIVATION_DATE}</td>
	</tr>
<!-- END referend_by -->
</table>
</div>
<br />
<br />
<!-- ELSE -->
<!-- ENDIF -->
<div class="category">
<div class="cat_title">
<center>
{L_YOUR_INVITES}
</center>
</div>
<table class="user_profile bordered w100" cellpadding="0" border="1">
<!-- IF INVITES_PRESENT -->
<tr>
	<th class="thHead">{L_INVITE_GET_DATE}</th>
	<th class="thHead">{L_INVITE_CODE}</th>
	<th class="thHead">{L_INVITE_ACTIVE}</th>
	<th class="thHead">{L_INVITE_INVITED_USER}</th>
	<th class="thHead">{L_INVITE_ACTIVATION_DATE}</th>
</tr>
<!-- BEGIN invite_row -->
	<tr>
	  <td class="row1" align="center">{invite_row.GENERATION_DATE}</td>
	  <td class="row2" align="center">{invite_row.INVITE_CODE}</td>
	  <td class="row1" align="center">{invite_row.ACTIVE}</td>
	  <td class="row1" align="center">{invite_row.NEW_USER}</td>
	  <td class="row1" align="center">{invite_row.ACTIVATION_DATE}</td>
	</tr>
<!-- END invite_row -->
<!-- ELSE -->
<tr>
	<td colspan="5" class="row1" align="center">{L_INVITE_NOT_GETED}</td>
</tr>
<!-- ENDIF -->
</table>
</div>

<!--bottom_info-->
<div class="bottom_info">
	<div class="spacer_8"></div>
	<div id="timezone">
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>
</div><!--/bottom_info-->
