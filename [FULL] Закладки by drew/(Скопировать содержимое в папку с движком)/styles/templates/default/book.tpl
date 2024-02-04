<script type="text/javascript">
	ajax.book = function (tid) {
		ajax.exec(
			{
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
			<a href="#" class="med normal" onclick="setCookie('bb_mark_read', 'all_forums');">Отметить все форумы как прочтённые</a>
		</td>
	</tr>
	</tbody>
</table>

<table class="forumline tablesorter">
	<thead>
	<tr>
		<th class="header"></th>
		<th class="{sorter: 'text'}"><b class="tbs-text">Тема</b></th>
		<th class="{sorter: 'text'}"><b class="tbs-text">Форум</b></th>
		<th class="{sorter: false}"><b class="tbs-text">Ответов</b></th>
		<th class="{sorter: false}">&nbsp;Удалить&nbsp;</th>
	</tr>
	</thead>
	<!-- BEGIN book -->
	<tr id="tr-{book.ID}" class="row2">
		<td id="{book.ID} tCenter" class="topic_id row1"><img class="topic_icon" src="./styles/templates/default/images/folder.gif" alt=""></td>
		<td class="row1 med bold w70">{book.TOPIC}</td>
		<td class="row1 med bold tCenter" style="width:30%;">{book.FORUM}</td>
		<td class="row1 med tCenter" style="width:30%;"><span title="Количество ответов">{book.REPLIES}</span> | <span title="Количество просмотров">{book.VIEWS}</span></td>
		<td class="row2 tCenter"><input type="submit" onclick="ajax.book('{book.ID}'); $('#tr-{book.ID}').hide();" value="Удалить"></td>
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
