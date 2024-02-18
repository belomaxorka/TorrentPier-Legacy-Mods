<!-- IF TPL_PERS_PARSER -->
<script type="text/javascript">
	$(document).ready(function () {
		ajax.Start = function () {
			var i = 0;
			ajax.callback.person = function (data) {
				if (data.html) {
					document.getElementById('results').innerHTML += data.html;
				}
				if ((data.idcount < i) || (data.stop)) {
					stop();
				}
			};
			var timerId = setInterval(function () {
				ajax.exec({action : 'person', step: i, type: 'auto_parser'});
				i++;
			}, 1000);

			function stop()

            {clearTimeout(timerId)}
		}
	});
</script>
<div id="infobox-wrap" class="bCenter row1">
	<fieldset class="pad_6">
		<legend class="med bold mrg_2 warnColor1">{L_AD_PERS_AUTO_PARSER}</legend>
		<div class="bCenter">
			<div id="infobox-body">
				<ul id="results"></ul>
			</div>
	</fieldset>
	<p class="gen tCenter pad_6"><a onclick="ajax.Start(); return false;" href="/" class="gen">[ {L_AD_PERS_PARSER_RUN} ]</a> <a href="javascript:window.close();" class="gen">[ {L_CLOSE_WINDOW} ]</a></p>
</div>
<!-- ENDIF / TPL_PERS_PARSER -->
<!-- IF TPL_PERS_POST_UP -->
<script type="text/javascript">
	ajax.update_post = function (mode) {
		ajax.exec({
			action: 'person',
			type: mode,
		});
	};
	ajax.callback.person = function (data) {
		$('#results').html(data.html);
	};
</script>
<div id="infobox-wrap" class="bCenter row1">
	<fieldset class="pad_6">
		<legend class="med bold mrg_2 warnColor1">ПЕРЕЗАПИСЬ ПОСТОВ</legend>
		<div class="bCenter">
			<div id="infobox-body">
				<ul id="results"></ul>
			</div>
	</fieldset>
	<p class="gen tCenter pad_6"><a onclick="ajax.update_post('up_post'); return false;" href="/" class="gen">[ {L_AD_PERS_SYC} ]</a> <a href="javascript:window.close();" class="gen">[ {L_CLOSE_WINDOW} ]</a></p>
</div>
<!-- ENDIF / TPL_PERS_POST_UP -->
<!-- IF TPL_PERS_EDIT -->
<h1>{PAGE_TITLE}</h1>
<p>{L_RANKS_EXPLAIN}</p>
<br/>
<script type="text/javascript">
	$(document).ready(function () {
		$('#pars').click(function () {
			var kp_id = $('#kp_id').val();
			if ((kp_id > 0) && (kp_id != '')) {
				ajax.exec({action: 'person', id: kp_id, type: 'filling'});
				ajax.callback.person = function (data) {
					$("input[name='runame']").val(data.runame);
					$("input[name='enname']").val(data.enname);
					$("#gender [value='" + data.gender + "']").attr("selected", "selected");
					$("input[name='foto']").val(data.foto);
					$("input[name='birthday']").val(data.birthdate);
					$("input[name='birthplace']").val(data.birthplace);
					$("input[name='career']").val(data.career);
				};
			} else {alert('{L_AD_PERS_PARSER_NOT_ID}')}
		});
	});
</script>
<form enctype="multipart/form-data" action="{S_RANK_ACTION}" method="post">
    {S_HIDDEN_FIELDS}
    <input type="hidden" name="mode" value="{MODE}" />
    <table class="forumline w100">
        <col class="row1">
        <col class="row2">
        <tr>
            <th colspan="2">{PAGE_TITLE}</th>
        </tr>
        <tr>
            <td valign="top" class="prof-title"><h4>{L_KP_ID}:</h4></td>
            <td>
                <input class="post" type="number" name="kp_id" id="kp_id" size="55" maxlength="6" value="{KP_ID}" />  <input type="button" value="Заполнить" name="Send" id="pars">
            </td>
        </tr>
        <tr>
            <td width="40%" class="prof-title"><h4>{L_PERS_RU_NAME}:*</h4></td>
            <td>
                <input class="post" type="text" name="runame" size="55" maxlength="90" value="{PERS_NAME_RU}" />
            </td>
        </tr>
        <tr>
            <td width="40%" class="prof-title"><h4>{L_PERS_EN_NAME}:*</h4></td>
            <td>
                <input class="post" type="text" name="enname" size="55" maxlength="90" value="{PERS_NAME_EN}" />
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
                <input class="post" type="text" name="foto" size="55" maxlength="255" value="{PERS_IMAGES}" />
            </td>
        </tr>
        <tr>
            <td width="40%" class="prof-title"><h4>{L_PERS_BIRTHDATE}:*</h4></td>
            <td>
                <input type="date" name="birthday" size="25" maxlength="10" placeholder="0000-00-00" value="{PERS_BIRTHDAY}" />
            </td>
        </tr>
        <tr>
            <td width="40%" class="prof-title"><h4>{L_PERS_BIRTHPLACE}:</h4></td>
            <td>
                <input class="post" type="text" name="birthplace" size="55" maxlength="90" value="{PERS_BIRTHPLACE}" />
            </td>
        </tr>
        <tr>
            <td valign="top" class="prof-title"><h4>{L_PERS_CAREER}:</h4></td>
            <td>
                <input class="post" type="text" name="career" size="55" maxlength="100" value="{PERS_CAREER}" />
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
                <input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp; &nbsp;
                <input type="reset" value="{L_RESET}" class="liteoption" />
            </td>
        </tr>
    </table>

</form>
<!-- ENDIF / TPL_PERS_EDIT -->

<!-- IF PERS_CONFIG -->
<h1>{L_AD_PERS_CONFIG}</h1>

<p>{L_AD_PERS_CONFIG_EXPLAIN}</p>
<br />

<a href="{U_PERS_ADD}" class="bold">{L_AD_PERS_ADD_NEW}</a> &#0183;
<a href="{U_PERS_POST_UP}" onclick="window.open(this.href, '', 'HEIGHT=430, WIDTH=760, resizable=yes'); return false;" class="bold">{L_AD_PERS_SYC}</a>&#0183;
<a href="{U_PERS_CONF}" class="bold">{L_AD_PERS_CONFIG}</a> &#0183;
<a href="{U_PERS_PARSER}" onclick="window.open(this.href, '', 'HEIGHT=430, WIDTH=760, resizable=yes'); return false;" class="bold">{L_AD_PERS_PARSER}</a>
<br /><br />
<br /><br />
<form action="{S_CONFIG_ACTION}" method="post">
    {S_HIDDEN_FIELDS}
    <table class="forumline">
        <col class="row1">
        <col class="row2">
        <tr>
            <th colspan="2">{L_AD_PERS_CONFIG}</th>
        </tr>
        <tr>
            <td><h4>{L_AD_PERS_ENABLE}</h4><h6>{L_AD_PERS_ENABLE_EXPLAIN}</h6></td>
            <td>
                <label><input type="radio" name="pers_enable" value="1" <!-- IF PERS_ENABLE_MOD -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="pers_enable" value="0" <!-- IF not PERS_ENABLE_MOD -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <tr>
            <td><h4>{L_AD_PERS_PER_PAGE}</h4></td>
            <td><input class="post" type="number" size="25" name="pers_per_page" value="{PERS_PER_PAGE}" /></td>
        </tr>
        <tr>
            <td><h4>{L_AD_PERS_REPLACE_TEXT}</h4></td>
            <td><input class="post" type="text" size="45" maxlength="255" name="pers_repl_text" value="{PERS_REPLACE_TEXT}"></td>
        </tr>
        <tr class="row3 med">
            <td class="bold tCenter" colspan="2">Настройки парсера персон с КиноПоиска</td>
        </tr>
        <tr>
            <td><h4>{L_AD_PERS_IDKP_LIST}</h4></td>
            <td><input class="post" type="text" size="45" name="pers_idkp_list" value="{PERS_IDKP_PARSER}"><br>{L_AD_PERS_IDKP_LIST_EXPLAIN}</td>
        </tr>
        <tr>
            <td><h4>{L_AD_PERS_PHOTO_DIR}</h4><h6>{L_AD_PERS_PHOTO_DIR_EXPLAIN}</h6></td>
            <td><input class="post" type="text" size="45" name="pers_photo_dir" value="{PERS_PARSER_PHOTO_DIR}"></td>
        </tr>
        <tr>
            <td class="catBottom" colspan="2">
                <input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;
                <input type="reset" value="{L_RESET}" class="liteoption" />
            </td>
        </tr>
    </table>
</form>

<br clear="all" />
<!-- ENDIF / PERS_CONFIG -->

<!-- IF TPL_PERS_LIST -->
<script type="text/javascript">
	ajax.callback.person = function (data) {
		if (data.hide) {
			$('tr#pers_' + data.pers_id).hide(300);
		}
	};
</script>
<h1>{L_AD_PERS_PANEL}</h1>

<p>{L_AD_PERS_PANEL_EXPLAIN}</p>
<br />

<a href="{U_PERS_ADD}" class="bold">{L_AD_PERS_ADD_NEW}</a> &#0183;
<a href="{U_PERS_POST_UP}" onclick="window.open(this.href, '', 'HEIGHT=430, WIDTH=760, resizable=yes'); return false;" class="bold">{L_AD_PERS_SYC}</a>&#0183;
<a href="{U_PERS_CONF}" class="bold">{L_AD_PERS_CONFIG}</a> &#0183;
<a href="{U_PERS_PARSER}" onclick="window.open(this.href, '', 'HEIGHT=430, WIDTH=760, resizable=yes'); return false;" class="bold">{L_AD_PERS_PARSER}</a>
<br /><br />
<table class="forumline">
    <tr>
        <th colspan="3">{L_PERS_LIST}</th>
    </tr>
    <!-- IF TPL_PERS_LIST_NO_ERROR -->
    <tr class="row3 med tLeft">
        <td width="5%">{L_PERS_ID}</td>
        <td>{L_PERS_NAME}</td>
        <td>{L_ACTION}</td>
    </tr>
    <!-- BEGIN person_list -->
    <tr id="pers_{person_list.PERS_ID}" class="{person_list.ROW_CLASS} hl-tr">
        <td nowrap="nowrap" align="center">{person_list.PERS_ID}</td>
        <td nowrap="nowrap" align="left">{person_list.PERS_NAME}</td>
        <td nowrap="nowrap" align="left">
            <a href="{person_list.U_PERS_IDIT}"><img src="{SITE_URL}styles/images/icon_edit.gif" alt="[Edit]" title="{L_EDIT}" /></a>
            <a href="/" onclick="ajax.exec({ action: 'person', pers_id: {person_list.PERS_ID}, type: 'delete'}); return false;"><img src="{SITE_URL}styles/images/icon_delete.gif" alt="[Del]" title="{{L_DELETE}}" /></a>
        </td>
    </tr>
    <!-- END person_list -->
    <!-- ELSE -->
    <tr class="row1">
        <td nowrap="nowrap" align="center" colspan="3"><span class="gen">&nbsp;{NO_ACTORS_LIST}&nbsp;</span></td>
    </tr>
    <!-- ENDIF / TPL_PERS_LIST_NO_ERROR -->
    <!-- IF PAGINATION -->
    <tr>
        <td colspan="3" class="catBottom {PG_ROW_CLASS}">
            <p style="float: left">{PAGE_NUMBER}</p>
            <p style="float: right">{PAGINATION}</p>
		</td>
    </tr>
    <!-- ENDIF -->
</table>
<!-- ENDIF / TPL_PERS_LIST -->
