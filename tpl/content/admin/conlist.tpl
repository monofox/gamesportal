{if $edit}<h3>Bearbeiten</h3>{else}<h3>Neuanlage</h3>{/if}
<form class="modern_form" action="admplatform/{if $edit}{$platid}/edit{else}new{/if}" method="post">
    <div class="leftbar">
        <label for="nPlatName">Name</label>
    </div>
    <div class="rightbar">
        <input id="nPlatName" type="text" name="platname" {if $edit}value="{$platname}"{/if} />
    </div>
    <div class="rightbar">
        {if $edit}
        <input type="hidden" name="platid" value="{$platid}"/>
        {/if}
        <input class="button" type="submit" value="Speichern" />
    </div>                    
    <br class="clear" />
</form>

<h3>Konsole bearbeiten</h3>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Aktion</th>
        </tr>
    </thead>
    <tbody>
{foreach from=$platforms->getData() key="k" item="i"}
        <tr>
            <td>{$i->platID}</td>
            <td>{$i->platName}</td>
            <td>
                <a href="admplatform/{$i->platID}/delete"><img src="res/icons/computer_delete.png" /></a>
                <a href="admplatform/{$i->platID}/edit"><img src="res/icons/computer_edit.png" /></a>
            </td>
        </tr>
{/foreach}
    </tbody>
</table>
