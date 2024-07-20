<script type="text/javascript">
	ajax.book = function (tid) {
		ajax.exec({
			action: 'book',
			mode: 'delete',
			tid: tid
		});
	};

	ajax.callback.book = function (data) {
		if (data.ok) {
			$('#tr-' + data.tid).hide();
		}
	};
</script>

<table cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td width="100%">
			<h1 class="pagetitle">{L_BOOKMARKS}</h1>
			<div id="forums_top_links" class="nav">
				<a href="{U_INDEX}">{T_INDEX}</a>
				<!-- IF LOGGED_IN -->
				<em>&middot;</em>
				<a href="#" class="med normal" onclick="setCookie('{COOKIE_MARK}', 'all_forums'); window.location.reload();">{L_MARK_ALL_FORUMS_READ}</a>
				<!-- ENDIF -->
			</div>
		</td>
	</tr>
</table>

<table class="forumline tablesorter">
	<thead>
	<tr>
		<th class="{sorter: 'text'}"></th>
		<th class="{sorter: 'text'}"><b class="tbs-text">{L_TOPIC}</b></th>
		<th class="{sorter: 'text'}"><b class="tbs-text">{L_FORUM}</b></th>
		<th class="{sorter: 'digit'}"><b class="tbs-text">{L_REPLIES}</b></th>
		<th class="{sorter: 'digit'}"><b class="tbs-text">{L_VIEWS}</b></th>
		<th class="{sorter: false}">&nbsp;{L_DELETE}&nbsp;</th>
	</tr>
	</thead>
	<!-- BEGIN book -->
	<tr id="tr-{book.ID}" class="{book.ROW_CLASS}">
		<td id="{book.ID}" class="topic_id tCenter">
			<span style="display: none;">{book.TOPIC_ICON}</span>
			<img class="topic_icon" src="{book.TOPIC_ICON}">
		</td>
		<td class="med bold w70"><!-- IF book.POLL --><span class="topicPoll">{L_TOPIC_POLL}</span>&nbsp;<!-- ENDIF -->{book.TOPIC}</td>
		<td class="med bold tCenter" style="width:30%;">{book.FORUM}</td>
		<td class="med tCenter" style="width:30%;"><span title="{L_REPLIES}: {book.REPLIES}">{book.REPLIES}</span></td>
		<td class="med tCenter" style="width:30%;"><span title="{L_VIEWED}: {book.VIEWS}">{book.VIEWS}</span></td>
		<td class="tCenter"><input type="submit" onclick="ajax.book('{book.ID}');" value="{L_DELETE}"></td>
	</tr>
	<!-- END book -->
	<!-- BEGIN no_book -->
	<tbody>
	<tr>
		<td class="row1 tCenter pad_8" colspan="6">{no_book.NO_BOOK}</td>
	</tr>
	</tbody>
	<!-- END no_book -->
	<tfoot>
	<tr>
		<!-- IF PAGINATION -->
		<td class="catBottom tLeft" colspan="6">
			<p style="float: left">{PAGE_NUMBER}</p>
			<p style="float: right">{PAGINATION}</p>
		</td>
		<!-- ELSE -->
		<td class="catBottom tLeft" colspan="6">&nbsp;</td>
		<!-- ENDIF -->
	</tr>
	</tfoot>
</table>
