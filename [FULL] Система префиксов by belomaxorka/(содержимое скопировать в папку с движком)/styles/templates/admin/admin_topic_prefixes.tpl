<!-- IF TPL_PREFIX_EDIT -->
<!--========================================================================-->

<h1>{L_PREFIX_ADMIN_TITLE_EDIT}</h1>
<br />

<form action="{S_PREFIX_ACTION}" method="post">
    {S_HIDDEN_FIELDS}

    <table class="forumline wAuto">
        <col class="row1">
        <col class="row2">
        <tr>
            <th colspan="2">{L_PREFIX_ADMIN_MANAGE}</th>
        </tr>
        <tr>
            <td width="40%"><h4>{L_PREFIX_ADMIN_NAME}</h4></td>
            <td>
                <input class="post" type="text" name="name" size="60" maxlength="40" value="{PREFIX_NAME}" />
            </td>
        </tr>
        <tr>
            <td width="40%"><h4>{L_PREFIX_ADMIN_DESC}</h4></td>
            <td>
                <input class="post" type="text" name="description" size="60" maxlength="40" value="{PREFIX_DESC}" />
            </td>
        </tr>
        <script type="text/javascript" src="{SITE_URL}styles/js/jscolor.min.js"></script>
        <tr>
            <td width="40%"><h4>{L_PREFIX_ADMIN_COLOR}</h4></td>
            <td>
                <input data-jscolor="{required:false}" class="post" type="text" name="color" maxlength="7"
                       value="{PREFIX_COLOR}"/><br/>
                <button type="button" onclick="$('input[data-jscolor]').val('');">{L_CLEAR}</button>
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

<!--========================================================================-->
<!-- ENDIF / TPL_PREFIX_EDIT -->

<!-- IF TPL_PREFIX_LIST -->
<!--========================================================================-->

<h1>{L_TOPIC_PREFIXES}</h1>

<p>{L_PREFIX_ADMIN_TITLE_DESC}</p>
<br />

<form method="post" action="{S_PREFIX_ACTION}">

    <table class="forumline w80">
        <tr>
            <th>{L_PREFIX_ADMIN_NAME}</th>
            <th>{L_PREFIX_ADMIN_DESC}</th>
            <th>{L_PREFIX_ADMIN_COLOR}</th>
            <th colspan="3">{L_ACTION}</th>
        </tr>
        <!-- BEGIN prefixes -->
        <tr class="{prefixes.ROW_CLASS} tCenter">
            <td>{prefixes.PREFIX_NAME}</td>
            <td>{prefixes.PREFIX_DESCRIPTION}</td>
            <!-- IF prefixes.PREFIX_COLOR -->
            <td title="{prefixes.PREFIX_COLOR}" style="background-color: {prefixes.PREFIX_COLOR};">{prefixes.PREFIX_COLOR}</td>
            <!-- ELSE -->
            <td>{L_PREFIX_ADMIN_PREFIX_COLOR_NOT_SELECTED}</td>
            <!-- ENDIF -->
            <td><a href="{prefixes.U_PREFIX_EDIT}">{L_EDIT}</a></td>
            <td><a href="{prefixes.U_PREFIX_DELETE}">{L_DELETE}</a></td>
        </tr>
        <!-- END prefixes -->
        <tr>
            <td class="catBottom" colspan="5">
                <input type="submit" class="mainoption" name="add" value="{L_PREFIX_ADMIN_ADD_NEW}" />
            </td>
        </tr>
    </table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_PREFIX_LIST -->
