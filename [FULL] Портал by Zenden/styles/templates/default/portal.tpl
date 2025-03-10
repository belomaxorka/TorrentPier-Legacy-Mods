<div id="latest_news" class="scrollContainer">
	<div class="tCenter scrollContainerNew">
		<form name="search" action="">
			<span>
				{L_SEARCH}:&nbsp;
				<select name="mode">
					<option value="title">по названию темы</option>
					<option value="user">по пользователю</option>
					<option value="desc">по описанию</option>
				</select>
				<input type="text" name="search" maxlength="20">&nbsp;
				<input type="submit" value="{L_GO}">
			</span>
		</form>
	</div>
</div>
<!-- IF PAGINATION -->
<div class="PageNav" id="pagination">
	<p style="float: left">{PAGE_NUMBER}</p>
	<p style="float: right">{PAGINATION}</p>
	<br/>
</div>
<!-- ENDIF -->
<!-- BEGIN no_topics -->
<table width="100%" cellpadding="2" border="0" class="forumline">
	<tr>
		<td class="tCenter row1 pad_8">{no_topics.DESCRIPTION}</td>
	</tr>
</table>
<!-- END no_topics -->
<!-- BEGIN topics -->
<div style="margin: 0px 0px 4px 0px;" class="messageList">
	<table width="100%" cellpadding="2" cellspacing="1" border="0">
		<div class="category">
			<div class="categoryStrip">
				<h3 class="nodeTitle">
					<a href="{topics.U_VIEW_TOPIC}">{topics.TOPIC_TITLE}</a>{topics.TOR_TYPE}
				</h3>
			</div>
		</div>
		<tr>
			<td class="tLeft" colspan="2" height="24">
				<span>
					{L_POSTED}: <a href="{PROFILE_URL}{topics.TOPIC_POSTER_ID}" class="topicAuthor"><b>{topics.POSTER}</b></a> 
					{topics.TIME}<br/>
				</span>
			</td>
		</tr>
		<tr>
			<td colspan="2"><div class="post_wrap"><div class="post_body">{topics.POSTER_IMG}{topics.DESCRIPTION}</div></div></td>
		</tr>
	</table>
	<table width="100%" cellpadding="2" cellspacing="1" border="0">
		<tr>
			<td class="tLeft" height="24"><input type="button" onclick="location.href='{topics.U_VIEW_TOPIC}'" value="{L_REPLIES}: {topics.REPLIES}"/><!-- IF topics.PORTAL_POST --> :: <!-- IF topics.TOR_FROZEN --><!-- ELSE --><input type="button" onclick="location.href='{DOWNLOAD_URL}{topics.ATTACH_ID}'" value="{L_DOWNLOAD}: {topics.SIZE}"/><!-- ENDIF --></span>
				 :: 
				<script type="text/javascript">
					ajax.callback.portal = function(data){$('#portal_'+data.topic_id).html(data.html);};
				</script>
				<input type="button" onclick="ajax.exec({ action: 'portal', topic_id: {topics.TOPIC_ID}});" value="Статистика" /><span id="portal_{topics.TOPIC_ID}"></span>
				{L_TOR_STATUS}: <span title="{topics.TOR_STATUS_TEXT}">{topics.TOR_STATUS_ICON}</span><!-- ENDIF -->
			</td>
		</tr>
	</table>
</div>
<!-- END topics -->
<table class="tCenter" width="100%" cellpadding="2" cellspacing="1" border="0">
	<!-- BEGIN data -->
	<tr>
		<!-- BEGIN topics -->
		<td widht="33%" class="messageList vTop">
			<table class="bCenter w100">
				<tr>
					<td>
						<div class="category">
							<div class="categoryStrip">
								<h3 class="nodeTitle">
									<a href="{topics.TOPIC_ID}">{topics.TOPIC_TITLE} {topics.IS_GOLD}</a>
								</h3>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<a href="{topics.TOPIC_ID}" alt="{topics.TOPIC_TITLE}" title="{topics.TOPIC_TITLE}" class="poster"><img src="{topics.POSTER}" style="margin-bottom:8px;margin-right:8px;max-width:200px;max-height:200px;" /></a>
					</td>
				</tr>
			</table>
			<table class="bCenter">
				<tr>
					<td valign="bottom">
						{L_REPLIES}<a href="{topics.TOPIC_ID}"> {topics.TOPIC_REPLIES}</a> :: <!-- IF topics.TOR_FROZEN --><!-- ELSE --><a href="{DOWNLOAD_URL}{topics.ATTACH_ID}"><!-- ENDIF --> {topics.SIZE}</a></span> :: <span class="leechmed">&dArr;{topics.LEECHERS} ({topics.SPEED_DOWN})</span>
						<span class="seedmed">&uArr;{topics.SEEDERS} ({topics.SPEED_UP})</span>
						<span title="{topics.TOR_STATUS}">{topics.TOR_ICONS}</span>
					</td>
				</tr>
			</table>
		</td>
		<!-- END topics -->
		<!-- BEGIN not -->
		<td class="row1 tCenter" width="25%">&nbsp;</td>
		<!-- END not -->
	</tr>
	<!-- END data -->
</table>
<!-- IF PAGINATION -->
<div class="PageNav" id="pagination">
	<p style="float: left">{PAGE_NUMBER}</p>
	<p style="float: right">{PAGINATION}</p>
	<br/>
</div>
<!-- ENDIF -->
