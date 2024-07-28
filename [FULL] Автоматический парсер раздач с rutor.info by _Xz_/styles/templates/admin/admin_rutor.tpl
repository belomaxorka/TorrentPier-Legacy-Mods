
<h1>Грабер новинок с Rutor.info</h1>

<p>Настройка учеток с которых будет копироваться раздачи и форумов откуда и куда</p>
<br />

<script type="text/javascript">
function change_poster_id(url, type)
{
	user = prompt("Введите user_id или username", "");
	if(user)
	{
		ajax.exec({
			action   : 'rutor',
			mode     : 'change_user_id',
			url      : url,
			user     : user,
			type     : type,
		});
	}
}
ajax.callback.rutor = function(data){
	if(data.mode == 'change_user_id')
	{
		$('span#'+ data.url).html(data.user);
	}
};
</script>

<table width="100%" class="forumline">
<thead>
<tr>
    <th width="5%">№</th>
    <th width="35%">Откуда</th>
    <th width="35%">Куда</th>
    <th width="15%">Пользователь</th>
    <th width="5%">Всю категорию</th>
    <th>{L_DELETE}</th>
</tr>
</thead>
<!-- BEGIN r_f -->
<tr class="{r_f.CLASS}" id="categorie-{r_f.NOMER}">
    <td class="tCenter"><input onclick="ajax.exec({ action: 'rutor', mode: 'active', categorie: '{r_f.CATEGORIE}'});" type="checkbox" name="active" value="1" <!-- IF r_f.ACTIVE -->{CHECKED}<!-- ENDIF --> />{r_f.NOMER}</td>
	<td><a target="_blank" href="{r_f.CATEGORIE}">{r_f.CATEGORIE}</a></td>
    <td class="tCenter">{r_f.FORUM}</td>
    <td class="tCenter"><span id="{r_f.MD5}">{r_f.USER}</span> <a href="#" onclick="change_poster_id('{r_f.CATEGORIE}', 'full'); return false;">[edit]</a></td>
    <td class="tCenter"><input onclick="ajax.exec({ action: 'rutor', mode: 'all_categorie', categorie: '{r_f.CATEGORIE}'});" type="checkbox" name="all_categorie" value="1" <!-- IF r_f.ALL_CATEGORIE -->{CHECKED}<!-- ENDIF --> /></td>
    <td class="tCenter"><img onclick="ajax.exec({ action: 'rutor', mode: 'categorie_del', categorie: '{r_f.CATEGORIE}'}); $('#categorie-{r_f.NOMER}').remove(); return false;" src="{SITE_URL}styles/images/bad.gif"></td>
</tr>
<!-- END r_f -->
<form method="post" action="admin_rutor.php?mode=add_categories">
<tr class="row3">
    <td></td>
    <td><input class="w100" type="text" name="categorie" value="{CATEGORIE}" /></td>
    <td>{FORUM}</td>
    <td><input class="w100" type="text" name="user_id" value="{USER}" /></td>
    <td class="tCenter"><input type="checkbox" name="all_categorie" value="1" <!-- IF not ALL_CATEGORIE -->{CHECKED}<!-- ENDIF --> /></td>
    <td class="tCenter"><input type="submit" name="submit" value="добавить" /></td>
</tr>
</form>
</table>
