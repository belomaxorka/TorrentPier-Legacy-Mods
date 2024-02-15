<h1 class="pagetitle">Закладки</h1>

<script type="text/javascript">
	ajax.book = function (tid) {
		ajax.exec({
			action: 'book',
			mode: 'delete',
			tid: tid
		});
	};

	ajax.callback.book = function (data) {
		if (data.info) alert(data.info);
		if (data.url) document.location.href = data.url;
	};
</script>
<table>
	<tbody>
	<tr>
		<td class="nav w100">
			<!-- IF LOGGED_IN --><a href="#" class="med normal" onclick="setCookie('{COOKIE_MARK}', 'all_forums'); window.location.reload();">{L_MARK_ALL_FORUMS_READ}</a><!-- ENDIF -->
		</td>
	</tr>
	</tbody>
</table>

<table class="forumline tablesorter">
	<thead>
	<tr>
		<th class="header"></th>
		<th class="{sorter: 'text'}"><b class="tbs-text">{L_TOPIC}</b></th>
		<th class="{sorter: 'text'}"><b class="tbs-text">{L_FORUM}</b></th>
		<th class="{sorter: false}"><b class="tbs-text">{L_REPLIES}</b></th>
		<th class="{sorter: false}">&nbsp;{L_DELETE}&nbsp;</th>
	</tr>
	</thead>
	<!-- BEGIN book -->
	<tr id="tr-{book.ID}" class="row2">
		<td id="{book.ID}" class="topic_id tCenter row1">
			<span style="display: none;">{book.TOPIC_ICON}</span>
			<img class="topic_icon" src="{book.TOPIC_ICON}">
		</td>
		<td class="row1 med bold w70">{book.TOPIC}</td>
		<td class="row1 med bold tCenter" style="width:30%;">{book.FORUM}</td>
		<td class="row1 med tCenter" style="width:30%;"><span title="{L_REPLIES}: {book.REPLIES}">{book.REPLIES}</span> | <span title="{L_VIEWED}: {book.VIEWS}">{book.VIEWS}</span></td>
		<td class="row2 tCenter"><input type="submit" onclick="ajax.book('{book.ID}'); $('#tr-{book.ID}').hide();" value="{L_DELETE}"></td>
	</tr>
	<!-- END book -->
	<!-- BEGIN no_book -->
	<tbody>
	<tr>
		<td class="row1 tCenter pad_8" colspan="9">Извините, у вас нет сохраненных закладок</td>
	</tr>
	</tbody>
	<!-- END no_book -->
	<tfoot>
	<tr>
		<td class="catBottom tLeft" colspan="5">&nbsp;</td>
	</tr>
	</tfoot>
</table>
