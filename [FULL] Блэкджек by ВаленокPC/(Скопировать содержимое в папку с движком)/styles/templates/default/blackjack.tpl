<!-- IF JS_ON -->
<script type="text/javascript">
	ajax.exec({action: 'bj'});
	ajax.callback.bj = function (data) {
		window.opener.document.getElementById('bjtable').innerHTML = data.html;
		window.opener.focus();
		window.close();
	};
</script>
<!-- ENDIF -->
<!-- IF MASSAGES_START -->
<script type="text/javascript">
	$('#bjbutt').live('click', function () {
		ajax.exec({action: 'bj'});
		ajax.callback.bj = function (data) {
			window.opener.document.getElementById('bjtable').innerHTML = data.html;
			window.opener.focus();
			window.close();
		};
	});
</script>
<table width="100%" cellpadding="10">
	<tr>
		<td>
			<table class="forumline">
				<tr>
					<th>{PAGE_TITLE}</th>
				</tr>
				<tr>
					<td class="row2 gen tCenter" colspan="2" style="border-bottom:none;">
						<p>
							Правило игры: набрать как можно больше очков, но не более 21.
						</p>
					</td>
				</tr>
				<tr>
					<td class="row1">
						<table class="borderless w100" cellpadding="5">
							<tr align="center">
								<td>{IMG_CARDS}</td>
							</tr>
							<tr>
								<td align="center"><b>Очки = {CARD_POINTS}</b></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="row2" align="center"><br/>
						<span class="med">
                            <form name="bj" method="post" action="blackjack.php"><input type="hidden" name="id" value='{ID_GAMES}'><input type="hidden" name="game" value="cont"><input type="submit" value="Ещё" class="btn"></form>
							<!-- IF STOP --><form name="bj" method="post" action="blackjack.php" onsubmit="this.submit.disabled = true;"><input type="hidden" name="id" value="{ID_GAMES}"><input type="hidden" name="game" value="stop"><input type="submit" name="submit" value="Хватит" class="btn" onkeypress="return false;"></form><!-- ENDIF -->
                        </span>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<!-- ENDIF -->
<!-- IF MASSAGES_INFO -->
<script type="text/javascript">
	$('#bjbutt').live('click', function () {
		ajax.exec({action: 'bj'});
		ajax.callback.bj = function (data) {
			window.opener.document.getElementById('bjtable').innerHTML = data.html;
			window.opener.focus();
			window.close();
		};
	});
</script>
<table width="100%" cellpadding="10">
	<tr>
		<td>
			<table class="forumline">
				<tr>
					<th>{TITLE}</th>
				</tr>
				<tr>
					<td class="row1">
						<table class="borderless w100" cellpadding="5">
							<tr align="center">
								<td>{MASSAGES}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="row2" align="center"><br/><span class="med"><a href="#" class="med" id="bjbutt">{L_CLOSE_WINDOW}</a></span></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<!-- ENDIF -->

<!-- IF GAMES_VIEW -->
<script type="text/javascript">
	$("#bjtable").click(function () {
		$(this).effect("pulsate", {times: 2}, 1000);
	});

	function UpdateBjTable() {
		ajax.exec({action: 'bj'});
		ajax.callback.bj = function (data) {
			$('#bjtable').html(data.html);
		};
	}

	function popupform(myform, windowname) {
		if (!window.focus) return true;
		var d = document.documentElement,
			h = 500,
			w = 500;
		window.open('', windowname, 'left=' + Math.max(0, ((d.clientWidth - w) / 2 + window.screenX)) + ', top=' + Math.max(0, ((d.clientHeight - h) / 2 + window.screenY)) + ', height=280, width=620, toolbar=no, status=no, scrollbars=no, resize=no, menubar=no');
		myform.target = windowname;
		return true;
	}
</script>

<br />

<table class="forumline" width=650 cellspacing=0 cellpadding=3>
    <tr><th class="cat_title" align="left" style="border-right:none">{BJ_GAME}</th>
        <th class="cat_title" align="right" style="border-left:none"><a href="#" onclick="UpdateBjTable(); return false;" style="text-decoration:none;" id="loader"><img src="styles/images/arr.gif" /> {L_UPDATE}</a></th>
    </tr>

    <tr><td class="row2 gen tCenter" colspan=2 style='border-bottom:none;'><p>
                    Приобрести жетоны можно в кредитном пункте.<br>
                        Жетоны снимаются с вас сразу после того как вы нажали на ставку.<br>
                        Если вы бросили игру или она у вас зависла Администрация не будет вам возвращать утерянные жетоны!<br>
                        Зависшие игры будут автоматически удалятся в течение 15/30 минут.</p>

        </td></tr>
    <tr><td class="row1 tCenter" colspan=2><br>
            <form name='blackjack' method='post' action='blackjack.php' onSubmit="popupform(this, 'join')">
                            <input type=hidden name=game value=start>
                <!-- BEGIN bet -->
                            <input type=submit name=bet style="width: 70px; height: 18px; background: #{bet.BET_COLOR}; color: #FFFFFF; font-weight: bold; border: 1px solid white" value='{bet.BET_GAMES}'>
                <!-- END bet -->
            </form>
    </td></tr>

    <tr>
        <td colspan=2 style="padding: 0; margin: 0;">
                <table class="forums" width='90%' cellpadding='2' cellspacing='0'>
                    <thead>
                        <tr class="row3">
                            <td align="center" width=15%><b>Начал</b></td>
                            <td align="center" width=20%><b>Время</b></td>
                            <td align="center" width=15%><b>Принял</b></td>
                            <td align="center" width=40%><b>Игра</b></td>
                        </tr>
                    </thead>
                    <tbody id="bjtable">
                    <!-- IF NO_GAMES -->
                        <!-- BEGIN waiting -->
                        <tr>
                            <td class="row1" width="15%" align="center">{waiting.PLACEHOLDER}</td>
                            <td class="row1" width="20%" align="center">{waiting.DATA_GAME}</td>
                            <td class="row1" width="15%" align="center">{waiting.GAMER}</td>
                            <td class="row1" width="40%" align="center">
                                <input type="button" style="width: 70px; height: 18px; background: #{waiting.COLOR_BET}; color: #FFFFFF; font-weight: normal; border: 1px solid white" {waiting.SELF} value='{waiting.BETS}' onClick="window.open('blackjack.php?takegame={waiting.GAME_ID}', '', 'height=280, width=620, toolbar=no, status=no, scrollbars=no, resize=no, menubar=no'); return false;">{waiting.W_PLAY}</td>
                        </tr>
                        <!-- END waiting -->
                        <!-- BEGIN finished -->
                        <tr><td class="row1 gen" {finished.BGCOLOR} width="15%" align="center">{finished.PLACEHOLDER}</td>
                            <td class="row1 gen" {finished.BGCOLOR} width="20%" align="center">{finished.DATA_GAME}</td>
                            <td class="row1 gen" {finished.BGCOLOR} width="15%" align="center">{finished.GAMER}</td>
                            <td class="row1 gen" {finished.BGCOLOR} width="40%" align="center">
                                <input type="button" style="width: 70px; height: 18px; background: #{finished.COLOR_BET}; color: #FFFFFF; font-weight: normal; border: 1px solid white" {finished.SELF} value='{finished.BETS}' onClick="window.open('blackjack.php?takegame={finished.GAME_ID}', '', 'height=280, width=620, toolbar=no, status=no, scrollbars=no, resize=no, menubar=no'); return false;">{finished.WINNER} {finished.GAME_WIN}</td>
                         </tr>
                        <!-- END finished -->
                    <!-- ELSE -->
                    <tr><td colspan=5 class="row1" width="15%" align="center">Нет Игр</td></tr>
                    <!-- ENDIF -->

                    </tbody>
                </table>
        </td>
    </tr>
</table>
<!-- ENDIF -->
