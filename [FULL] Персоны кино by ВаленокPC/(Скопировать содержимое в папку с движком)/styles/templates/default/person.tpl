<!-- IF TPL_PERS_EDIT -->
<h1>{L_PERS_EDIT}</h1>
<br/>

<form enctype="multipart/form-data" action="{S_RANK_ACTION}" method="post">
    {S_HIDDEN_FIELDS}
	<input type="hidden" name="mode" value="{MODE}"/>
	<table class="forumline w100">
		<col class="row1">
		<col class="row2">
		<tr>
			<th colspan="2">{PAGE_TITLE}</th>
		</tr>
		<tr>
			<td valign="top" class="prof-title"><h4>{L_KP_ID}:</h4></td>
			<td>
				<input class="post" type="number" name="kp_id" size="55" maxlength="6" value="{KP_ID}"/>
			</td>
		</tr>
		<tr>
			<td width="40%" class="prof-title"><h4>{L_PERS_RU_NAME}:*</h4></td>
			<td>
				<input class="post" type="text" name="runame" size="55" maxlength="90" value="{PERS_NAME_RU}"/>
			</td>
		</tr>
		<tr>
			<td width="40%" class="prof-title"><h4>{L_PERS_EN_NAME}:*</h4></td>
			<td>
				<input class="post" type="text" name="enname" size="55" maxlength="90" value="{PERS_NAME_EN}"/>
			</td>
		</tr>
		<tr>
			<td width="40%" class="prof-title"><h4>{L_GENDER}:*</h4></td>
			<td>
                {PERS_GENDER}
			</td>
		</tr>
		<tr>
			<td width="40%" class="prof-title"><h4>{L_PERS_FOTO}:</h4></td>
			<td>
				<input class="post" type="text" name="foto" size="55" maxlength="255" value="{PERS_IMAGES}"/>
			</td>
		</tr>
		<tr>
			<td width="40%" class="prof-title"><h4>{L_PERS_BIRTHDATE}:*</h4></td>
			<td>
				<input type="date" name="birthday" size="25" maxlength="10" placeholder="0000-00-00" value="{PERS_BIRTHDAY}"/>
			</td>
		</tr>
		<tr>
			<td width="40%" class="prof-title"><h4>{L_PERS_BIRTHPLACE}:</h4></td>
			<td>
				<input class="post" type="text" name="birthplace" size="55" maxlength="90" value="{PERS_BIRTHPLACE}"/>
			</td>
		</tr>
		<tr>
			<td valign="top" class="prof-title"><h4>{L_PERS_CAREER}:</h4></td>
			<td>
				<input class="post" type="text" name="career" size="55" maxlength="100" value="{PERS_CAREER}"/>
			</td>
		</tr>
		<tr>
			<td valign="top" class="prof-title"><h4>{L_PERS_BIOGRAPHY}:</h4></td>
			<td>
				<textarea rows="10" cols="65" name="biography">{PERS_BIOGRAPHY}</textarea>
			</td>
		</tr>
		<tr>
			<td class="catBottom" colspan="2">
				<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption"/>&nbsp;&nbsp;
				<input type="reset" value="{L_RESET}" class="liteoption"/>
			</td>
		</tr>
	</table>
</form>
<!-- ENDIF / TPL_PERS_EDIT -->


<!-- IF TPL_PERS_VIEW -->
<script type="text/javascript">
	ajax.callback.person = function (data) {
		if (data.redirect) document.location.href = data.redirect;
	};
</script>
<table cellpadding="0" class="w100" style="padding-top: 2px;">
	<tr>
		<td class="nav w100" style="padding-left: 8px;">
			<a href="{U_INDEX}">{L_HOME}</a>&nbsp;<em>&raquo;</em>
			<a href="{U_PERSON}">{L_PERS_PERSONS}</a>&nbsp;<em>&raquo;</em>
			<a href="{U_PERS_URL}">{PERS_NAME_RU}</a>
		</td>
	</tr>
</table>
<table class="user_profile bordered w100" cellpadding="0">
	<tr>
		<th colspan="2" class="thHead">{L_PERS_INFO}</th>
	</tr>
	<tr class="forumline pagetitle w7">
		<td colspan="2"><h1>{PERS_NAME_RU}</h1><!-- IF PERS_NAME_EN --><span>{PERS_NAME_EN}</span><!-- ENDIF --></td>
	</tr>
	<tr>
		<td class="row1 vTop tCenter" width="5%">
			<div id="avatar-img" class="mrg_4 med">
				<img title="{PERS_NAME_RU} <!-- IF PERS_NAME_EN -->({PERS_NAME_EN})<!-- ENDIF -->" width="240px" height="385px" src="{PERS_IMAGES}" alt="{PERS_NAME_RU} <!-- IF PERS_NAME_EN -->({PERS_NAME_EN})<!-- ENDIF -->">
			</div>
			<div class="spacer_4"></div>
			<h4 class="cat border bw_TB" id="username">{L_DISPLAYING_OPTIONS}</h4>
			<table class="nowrap borderless user_contacts w100">
				<!-- IF KP_ID -->
				<tr>
					<td class="tLeft med"><a href="http://kinopoisk.ru/name/{KP_ID}/" target="_blank">{L_KP_STR}</a></td>
				</tr>
				<!-- ENDIF -->
				<!-- IF IS_ADMIN -->
				<tr>
					<td class="tLeft med">
						<a href="{U_PERS_EDIT}">{L_EDIT}</a>
					</td>
				</tr>
				<tr>
					<td class="tLeft med"><a href="#" onclick="ajax.exec({ action: 'person', pers_id: {PERS_ID}, type: 'delete', redir:'{U_PERSON}'}); return false;">Удалить</a></td>
				</tr>
				<!-- ENDIF -->
			</table>
		</td>
		<td class="row1" valign="top" width="95%">
			<div class="spacer_4"></div>
			<table class="borderless w100">
				<tr>
					<th class="bold tRight vTop" width="10%">{L_USERNAME}:</th>
					<td>{PERS_NAME_RU} <!-- IF PERS_NAME_EN --><span class="small">({PERS_NAME_EN})</span></td>
					<!-- ENDIF -->
				</tr>
				<tr>
					<th class="bold tRight vTop" width="8%">{L_PERS_BIRTHDATE}:</th>
					<td>{BIRTHDATA}, {AGE} {ZODIAC_SIGN}</td>
				</tr>
				<tr>
					<th class="bold tRight vTop" width="10%">{L_GENDER}:</th>
					<td>{PERS_GENDER}</td>
				</tr>
				<!-- IF PERS_BIRTHPLACE -->
				<tr>
					<th class="bold tRight vTop" width="10%">{L_PERS_BIRTHPLACE}:</th>
					<td>{PERS_BIRTHPLACE}</td>
				</tr>
				<!-- ENDIF -->
				<!-- IF PERS_CAREER -->
				<tr>
					<th class="bold tRight vTop" width="10%">{L_PERS_CAREER}:</th>
					<td>{PERS_CAREER}</td>
				</tr>
				<!-- ENDIF -->
				<!-- IF PERS_BIOGRAPHY -->
				<tr>
					<th class="bold tRight vTop" width="10%">{L_PERS_BIOGRAPHY}:</th>
					<td>
						<div class="wAuto">{PERS_BIOGRAPHY}</div>
					</td>
				</tr>
				<!-- ENDIF -->
			</table>
		</td>
	</tr>
</table><br>
<table class="bordered w100">
	<th class="thHead gen clickable bold" style="font-size: 12px;" onclick="toggle_block('relesing'); return false;">{L_PERS_LIST_TORR}</th>
	<!-- IF DIST_ACTOR -->
	<tr id="relesing">
		<td class="td2 row2 tCenter">
			<table width="100%" class="forumline tablesorter">
				<thead>
				<tr>
					<th width="25%"><b class="tbs-text">{L_FORUM}</b></th>
					<th width="75%"><b class="tbs-text">{L_TOPIC}</b></th>
					<th width="100"><b class="tbs-text">{L_AUTHOR}</b></th>
					<th>{L_TORRENT}</th>
					<th>{L_REPLIES_SHORT}</th>
				</tr>
				</thead>
				<!-- BEGIN distribution -->
				<tr class="tCenter row1 med" id="tor_{distribution.POST_ID}">
					<td><a class="med" href="{distribution.U_FORUM}" class="genmed">{distribution.FORUM_NAME}</a></td>
					<td class="tLeft">
						<a title="{distribution.FULL_TOPIC_TITLE}" class="med" href="{distribution.U_VIEW_TOPIC}">
							<!-- IF distribution.TOR_FROZEN -->
                            {distribution.TOPIC_TITLE}
							<!-- ELSE -->
							<b>{distribution.TOPIC_TITLE}</b>
							<!-- ENDIF -->
						</a> {distribution.TOR_STATUS_ICON}
						<!-- IF distribution.PAGINATION -->
						<span class="topicPG">&nbsp;[&nbsp;{ICON_GOTOPOST}{L_GOTO_SHORT}&nbsp;{distribution.PAGINATION}&nbsp;]</span>
						<!-- ENDIF -->
					</td>
					<td>{distribution.AUTHOR}</td>
					<td class="tCenter nowrap" style="padding: 2px 4px;">
						<span class="seedmed" title="{L_SEEDERS}"><b>{distribution.SEEDERS}</b></span><span class="med"> | </span><span
							class="leechmed" title="{L_LEECHERS}"><b>{distribution.LEECHERS}</b></span>
						<div class="spacer_2"></div>
						<!-- IF distribution.TOR_FROZEN -->
						{distribution.TOR_SIZE}
						<!-- ELSE -->
						<a title="{L_DL_TORRENT}" href="{DOWNLOAD_URL}{distribution.ATTACH_ID}" class="small si-dl">{distribution.TOR_SIZE}</a>
						<!-- ENDIF -->
					</td>
					<td class="tCenter small nowrap" style="padding: 3px 4px 2px;">
						<p>
							<span title="{L_REPLIES}">{distribution.REPLIES}</span>
							<span class="small"> | </span>
							<span title="{L_VIEWS}">{distribution.VIEWS}</span>
						</p>
						<p style="padding-top: 2px" class="med" title="{L_COMPLETED}">
							<b>{distribution.COMPL_CNT}</b>
						</p>
					</td>
				</tr>
				<!-- END distribution -->
			</table>
		</td>
	</tr>
	<!-- ELSE -->
	<tr id="relesing">
		<td class="td2 row2 tCenter">
            {L_PERS_NO_TORR_LIST}
		</td>
	</tr>
	<!-- ENDIF -->
</table>
<!-- ENDIF / TPL_PERS_VIEW -->

<!-- IF TPL_PERS_LIST -->
<script type="text/javascript">
	ajax.callback.person = function (data) {
		if (data.hide) {
			$('li#pers_' + data.pers_id).hide(300);
		}
	};
</script>
<table cellpadding="0" class="w100" style="padding-top: 2px;">
	<tr>
		<td class="nav w100" style="padding-left: 8px;">
			<a href="{U_INDEX}">{L_HOME}</a>&nbsp;<em>&raquo;</em>
			<a href="{U_PERSON}">{L_PERS_PERSONS}</a>
		</td>
	</tr>
</table>
<table class="forumline">
	<tr>
		<th colspan="2" class="thHead">{L_PERS_LIST}</th>
	</tr>
	<tr class="prow2">
		<td align="right" class="med" colspan="2">{L_SORT_PER_LETTER}:&nbsp;
			<strong style="font-size: 0.95em;">
				<!-- BEGIN first_char -->
				<!-- IF first_char.S_SELECTED -->
				<b>{first_char.DESC}</b>&nbsp;
				<!-- ELSE -->
				<a href="{first_char.U_SORT}">{first_char.DESC}</a>&nbsp;
				<!-- ENDIF -->
				<!-- END first_char -->
			</strong>
		</td>
	</tr>
	<!-- IF TPL_PERS_LIST_NO_ERROR -->
	<tr>
		<td height="100%" class="row1 pad_4">
			<ui class="listPersons bCenter">
				<!-- BEGIN pers_list -->
				<li id="pers_{pers_list.PERS_ID}">
					<a href="{pers_list.U_ACTOR}" title="{pers_list.RU_NAME} ({pers_list.EN_NAME})" class="thumb">
						<img src="{pers_list.IMAGES}" alt="{pers_list.RU_NAME} ({pers_list.EN_NAME})"/>
						<span class="base">
                            <span class="nameru">{pers_list.RU_NAME}</span>
                            <p class="nameen">
                                {pers_list.EN_NAME}
                            </p>
                        </span>
					</a>
					<!-- IF IS_ADMIN -->
					<span class="action">
                        <span class="floatL"><a href="{pers_list.U_PERS_IDIT}">{L_EDIT}</a></span>
                        <span class="floatR"><a href="#" onclick="ajax.exec({ action: 'person', pers_id: {pers_list.PERS_ID}, type: 'delete'}); return false;">{L_DELETE}</a></span>
                    </span>
					<!-- ENDIF -->
				</li>
				<!-- END pers_list -->
			</ui>
		</td>
	</tr>
	<!-- ELSE -->
	<tr>
		<td class="row1" align="center" colspan="9"><span class="gen">&nbsp;{NO_PERSON_LIST}&nbsp;</span></td>
	</tr>
	<!-- ENDIF / TPL_PERS_LIST_NO_ERROR -->
	<!-- IF PAGINATION -->
	<tr>
		<td class="catBottom {PG_ROW_CLASS}">
			<p style="float: left">{PAGE_NUMBER}</p>
			<p style="float: right">{PAGINATION}</p>
		</td>
	</tr>
	<!-- ENDIF -->
</table>
<!-- ENDIF / TPL_PERS_LIST -->
