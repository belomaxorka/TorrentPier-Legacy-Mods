<script type="text/javascript">
	ajax.callback.del_imgs = function(data)
	{
		$('span#del_imgs_'+data.del_imgs).html(data.html);
		$('div#gen_tags').html(data.gen_tags);
	}
</script>
<style type="text/css">.poster{ max-width: 300px;max-height: 450px;}</style>
<div class="nav">
	<p class="floatR">
		<a href="gallery.php?dir={SESSION_USER_ID}">{L_MY_IMGS}</a>
	</p>
		<div class="clear"></div>
</div>
<table cellpadding="3" cellspacing="1" border="0" class="bordered w100">
	<tr> 
		<td class="catTitle" nowrap>{L_GALLERY}</td>
	</tr>
	<tr>
		<td class="row1">
			<div class="mrg_4 floatL w50">
				<span class="bold">{L_ALLOWED_EXT}:</span> {IMG_ALLOWED_EXT}<br/>
				<span class="bold">{L_MAX_SIZE}:</span> {IMG_MAX_SIZE}<br/>
				<!-- IF USER_DIR --><span class="bold">{L_ALL_UPLOAD}:</span> {COUNT_IMGS}<!-- ENDIF / USER_DIR -->
			</div>
			<div class="mrg_4 bLeft">
				<span class="bold">{L_MAX_HEIGHT}:</span> {$bb_cfg['imgs']['max_height']}<br/>
				<span class="bold">{L_MAX_WIDTH}:</span> {$bb_cfg['imgs']['max_width']}<br/>
				<!-- IF USER_DIR --><span class="bold">{L_DIR_SIZE}:</span> {USER_DIR_SIZE}<!-- ENDIF / USER_DIR -->
			</div>
			
		</td>
	</tr>

	<tr class="row1">
		<td>
			<!-- BEGIN upload -->
			<table id="delete_{upload.ID}" class="w100">
				<tr>
					<td width="310px" class="tCenter">
						<img class="poster mrg_4" src="<!-- IF upload.THUMB -->{SITE_URL}{upload.THUMB}<!-- ELSE -->{upload.IMG}<!-- ENDIF / upload.THUMB -->">
					</td>
					<td>
						<input type="text" onClick="this.select();" readonly value='{SITE_URL}{upload.IMG}' class="w90 mrg_4"><br/>
						<input type="text" onClick="this.select();" readonly value='[img]{SITE_URL}{upload.IMG}[/img]' class="w90 mrg_4"><br/>
						<!-- IF upload.THUMB -->
						<input type="text" onClick="this.select();" readonly value='[url={SITE_URL}{upload.IMG}][img]{SITE_URL}{upload.THUMB}[/img][/url]' class="w90 mrg_4"><br/>
						<!-- ENDIF / upload.THUMB -->
						<input type="text" onClick="this.select();" readonly value='[img=right]{SITE_URL}{upload.IMG}[/img]' class="w90 mrg_4"><br/>
						<input type="text" onClick="this.select();" readonly value='[spoiler="{L_SCREENSHOTS}"][img]{SITE_URL}{upload.IMG}[/img][/spoiler]' class="w90 mrg_4"><br/>
						<span id="del_imgs_{upload.ID}" title="{L_DEL_IMGS}" class="clickable tCenter gen bold" onclick="ajax.exec({action: 'del_imgs', imgs: '{BB_ROOT}{upload.IMG}<!-- IF upload.THUMB -->, {BB_ROOT}{upload.THUMB}<!-- ENDIF / upload.THUMB -->', all_imgs: $('input#all_imgs').val() }); $('#delete_{upload.ID}').hide(); return false">{L_DELETE}</span>
					</td>
				</tr>
			</table>	
			<!-- END upload -->

			<!-- IF USER_DIR -->
			<!-- BEGIN dir -->
			<table id="delete_{dir.ID}" class="w100">
				<tr>
					<td width="310px" class="tCenter">
						<img class="poster mrg_4" src="{dir.IMG}"><br/>
						<span class="floatL"><b>{L_SIZE}</b>: {dir.IMG_SIZE}</span><br/>
						<span class="floatL"><b>{L_GROUP_TIME}</b>: {dir.IMG_TIME}</span>
					</td>
					<td>
						<input type="text" onClick="this.select();" readonly value='{SITE_URL}{dir.IMG}' class="w90 mrg_4"><br/>
						<input type="text" onClick="this.select();" readonly value='[img]{SITE_URL}{dir.IMG}[/img]' class="w90 mrg_4"><br/>
						<span id="del_imgs_{dir.ID}" title="{L_DEL_IMGS}" class="clickable tCenter gen bold" onclick="ajax.exec({action: 'del_imgs', imgs: '{BB_ROOT}{dir.IMG}'}); $('#delete_{dir.ID}').hide(); return false">{L_DELETE}</span>
					</td>
				</tr>
			</table>
			<!-- END dir -->

			<!-- IF PAGINATION -->
			<div class="PageNav" id="pagination">
				<p class="floatR">{PAGINATION}</p>
				<div class="clear"></div>
			</div>
			<!-- ENDIF -->
			<!-- ENDIF / USER_DIR -->

			<!-- IF IMGS_ALL_TAGS -->
			<div class="tCenter" id="gen_tags">			
				<input type="text" onClick="this.select();" readonly value='{IMGS_ALL}' class="mrg_4 w90" name="all_imgs" id="all_imgs" /><br/>
				<input type="text" onClick="this.select();" readonly value='{IMGS_TAGS_IMG}' class="mrg_4 w90" /><br/>
				<!-- IF IMGS_TAGS_THUMB -->
				<input type="text" onClick="this.select();" readonly value='{IMGS_TAGS_THUMB}' class="mrg_4 w90" /><br/>
				<!-- ENDIF / IMGS_TAGS_THUMB -->
				<input type="text" onClick="this.select();" readonly value='[spoiler="{L_SCREENSHOTS}"]{IMGS_TAGS_SPOILER}[/spoiler]' class="mrg_4 w90" />
			</div>
			<!-- ENDIF / IMGS_ALL_TAGS -->
		</td>
	</tr>

	<tr>
		<td class="catBottom" colspan="2">
			<form enctype="multipart/form-data" method="post" id="data" action="gallery.php">
				<input type="hidden" name="IMG_MAX_SIZE" value="IMG_MAX_SIZE" />
				<input type="file" name="imgfile[]" multiple='true' />
				<input type="checkbox" name="thumb" value="0" />{L_CREATE_THUMBNAIL}
				<input type="submit" value="{L_UPLOAD_IMAGE}" title="{L_SELECT_IMGS}" />
			</form>
		</td>
	</tr>
</table>