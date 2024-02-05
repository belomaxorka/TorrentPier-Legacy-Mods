
<table cellpadding="2" cellspacing="0" width="100%">
<tr>
	<td width="100%">
		<h1 class="maintitle">{PAGE_TITLE}</h1>
        <div id="forums_top_links" class="nav">
		    <a href="{U_INDEX}">{T_INDEX}</a>&nbsp;<em>&middot;</em>
			<a href="warnings.php?warnings">Весь список</a>
			<!-- IF USER_ID --><em>&middot;</em>&nbsp;<a href="warnings.php?warnings&u={USER_ID}">Все предупреждения пользователя</a><!-- ENDIF -->
		</div>
	</td>
	<td class="vBottom tLeft nowrap med"><b>{PAGINATION}</b></td>
</tr>
</table>

<script language="Javascript" type="text/javascript">
ajax.openedPosts = {};
ajax.view_post = function(post_id, src) {
	if (!ajax.openedPosts[post_id]) {
		ajax.exec({
			action  : 'view_post',
			post_id : post_id
		});
	}
	else {
		var $post = $('#post_'+post_id);
		if ($post.is(':visible')) {
			$post.hide();
		}	else {
			$post.css({ display: '' });
		}
	}
};

ajax.callback.view_post = function(data) {
	var post_id = data.post_id;
	var $war = $('#war_'+post_id);
	window.location.href='#war_'+post_id;
	$('#post-row tr')
		.clone()
		.attr({ id: 'post_'+post_id })
		.find('div.post_body').html(data.post_html).end()
		.find('a.tLink').attr({ href: $('a.txtb', $war).attr('href') }).end()
		.insertAfter($war)
	;
	initPostBBCode('#post_'+post_id);
	var maxH   = screen.height - 290;
	var maxW   = screen.width;
	var $post  = $('div.post_wrap', $('#post_'+post_id));
	var $links = $('div.post_links', $('#post_'+post_id));
	$post.css({ maxWidth: maxW, maxHeight: maxH });
	$links.css({ maxWidth: maxW });
	if ($.browser.msie) {
		if ($post.height() > maxH) { $post.height(maxH); }
		if ($post.width() > maxW)  { $post.width(maxW); $links.width(maxW); }
	}
	ajax.openedPosts[post_id] = true;
};
</script>

<style type="text/css">
.post_wrap { border: 1px #A5AFB4 solid; margin: 8px 8px 6px; overflow: auto; }
.post_links { margin: 6px; }
</style>

<table id="post-row" style="display: none;">
<tr>
	<td class="row2" colspan="11">
		<div class="post_wrap row1">
			<div class="post_body pad_6"></div>
			<div class="clear"></div>
		</div>
		<div class="post_links med bold tCenter"><a class="tLink">Перейти к сообщению</a></div>
	</td>
</tr>
</table>

<!-- IF WARNINGS -->
<table width="100%" class="forumline tablesorter">
<thead>
<tr>
    <th class="{sorter: 'text'}"><b class="tbs-text">Пользователь</b></th>
    <th width="300" class="{sorter: 'text'} nowrap"><b class="tbs-text">Запрет / Дата окончания</b></th>
    <th class="{sorter: 'text'}" width="55%"><b class="tbs-text">Причина</b></th>
    <th width="200" class="{sorter: 'text'}"><b class="tbs-text nowrap">{L_ONLINE_MOD} / {L_ONLINE_ADMIN}</b></th>
</tr>
</thead>
<!-- BEGIN warning -->
<tr class="tCenter {warning.ROW_CLASS}" id="war_{warning.TYPE_ID}">
	<td>{warning.WARNING_USER}</td>
    <td class="med tLeft nowrap">
        {warning.WARNING}
	</td>
    <td class="med tLeft vTop">
	    <!-- IF warning.AUTH --><span class="floatR"><a href="warnings.php?id={warning.WARNING_ID}">изменить</a></span><!-- ENDIF -->
	    {warning.WARNING_TEXT}
	    <hr><span class="floatR">выдано {warning.TYPE}</span>
	</td>
    <td>{warning.POSTER_USER}</td>
</tr>
<!-- END warning -->
</table>
<!-- ENDIF -->

<!-- IF EDIT -->
<script type="text/javascript">
function warning(id, type, term, time, reason, auth) {	if(auth == '-1')
	{
		if(!confirm('Вы уверены, что хотите удалить данный запрет?')) return false;
	}
	ajax.warning(id, type, term, time, reason, auth);
}
ajax.warning = function(id, type, term, time, reason, auth) {
	ajax.exec({
		action  : 'warning',
		mode    : 'edit',
		id      : id,
		warning : type,
		term	: term,
		time	: time,
		reason  : reason,
		auth    : auth,
	});
};
ajax.callback.warning = function(data) {
    if(data.info) alert(data.info);
    if(data.url) document.location.href = data.url;
};
</script>
<table width="100%" class="forumline row1">
<tr>
  <th colspan="3" class="thHead">Редактирование запрета</th>
</tr>
<tr>
     <td width="20%" class="tRight"><b>Изменить наказание:</b></td>
     <td width="60%"><span id="auth">{AUTH}</span><span title="Тип блокировки" id="type">{WARNING}</span><input title="Время блокировки" id="term" type="text" maxlength="2" size="2" name="term" value="{TERM}"><span id="time">{TIME}</span></td>
     <td width="20%" rowspan="2" class="tRight">{WARNING_USER}<div class="spacer_6"></div>{AVATAR}</td>
</tr>
<tr id="war_{TYPE_ID}" class="vTop pad_4">
	<td align="right" width="25%"><b>Причина:</b>
	<td title="Причина блокировки" class="row2genmed">
		<textarea id="reason" name="reason" rows="5" cols="80%">{REASON}</textarea>
	    <hr><span class="floatR">выдано {TYPE}</span>
	</td>
</tr>
<tr>
	<td colspan="3" class="cat tCenter pad_4">
		<input onclick="warning({ID}, $('#type option:selected').val(), $('#term').val(), $('#time option:selected').val(), $('#reason').val(), $('#auth option:selected').val() ); return false;" type="button" value="Редактировать">
	</td>
</tr>
</tr>
</table>
<!-- ENDIF -->
