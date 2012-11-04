{if $edit}<h3>Bearbeiten</h3>{else}<h3>Neuanlage</h3>{/if}
<form class="modern_form" action="admgames/{if $edit}{$game->getId()}/edit{else}new{/if}" method="post" enctype="multipart/form-data">
    <div class="leftbar">
        <label for="idGameTitle">Titel</label>
    </div>
    <div class="rightbar">
        <input id="idGameTitle" type="text" name="gameTitle" value="{$game->getTitle()}" />
    </div>
    <div class="leftbar">
        <label for="idGameUSK">USK</label>
    </div>
    <div class="rightbar">
        <input id="idGameUSK" type="text" name="gameUSK" value="{$game->getUSK()}" />
    </div>
    <div class="leftbar">
        <label for="idGameDesc">Beschreibung</label>
    </div>
    <div class="rightbar">
        <textarea id="idGameDesc" name="gameDesc" rows="5" cols="30">
        {$game->getDescription()}
        </textarea>
    </div>
    <div class="leftbar">
        <label for="idGameFeatures">Funktionen</label>
    </div>
    <div class="rightbar">
       <input id="idGameFeatures" type="text" name="gameFeatures" value="{$game->getFeatures()}" />
    </div>
    <div class="leftbar">
        <label for="idGameLanguages">Sprachen</label>
    </div>
    <div class="rightbar">
        <select id="idGameLanguages" name="gameLanguages[]" multiple="multiple">
            {foreach from=$languages key='kLang' item='iLang'}
                <option {if $iLang|in_array:$game->getLanguages()}selected="selected"{/if} 
                value="{$iLang->getCode()}">{$iLang->getText()}</option>
            {/foreach}
        </select>
    </div>
    <div class="leftbar">
        <label for="idGamePlatforms">Plattformen</label>
    </div>
    <div class="rightbar">
        <select id="idGamePlatforms" name="gamePlatforms[]" multiple="multiple">
            {foreach from=$platforms key='kPlat' item='iPlat'}
               <option {if $iPlat->platID|in_array:$game->getListOfPlatforms()}selected="selected"{/if} 
                value="{$iPlat->platID}">{$iPlat->platName}</option>
            {/foreach}
        </select>
    </div>
    <div class="leftbar">
        <label for="idGameCompat">Plattform-Kompatibilitäten</label>
    </div>
    <div class="rightbar">
        <select id="idGameCompat" name="gameCompats[]" multiple="multiple">
            {foreach from=$platforms key='kPlat' item='iPlat'}
                <option {if $iPlat->platID|in_array:$game->getListOfCompats()}selected="selected"{/if} 
                value="{$iPlat->platID}">{$iPlat->platName}</option>
            {/foreach}
        </select>
    </div>
    <div class="leftbar">
        <label for="idGameCover">Cover</label>
    </div>
    <div class="rightbar">
        <input type="file" id="idGameCover" name="gameCover" />
        <input type="hidden" name="gameCoverId" value="{if $game->getCover() != null}{$game->getCover()->getId()}{/if}" />
    </div>

    <div class="rightbar">
        {if $edit}
        <input type="hidden" name="gameid" value="{$game->getId()}"/>
        {/if}
        <input class="button" type="submit" value="Speichern" />
    </div>                    
    <br class="clear" />
</form>

<h3>Spiele</h3>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Aktion</th>
        </tr>
    </thead>
    <tbody>
{foreach from=$games->getData() key="k" item="i"}
        <tr>
            <td>{$i->getId()}</td>
            <td>{$i->getTitle()}</td>
            <td>
                <a href="admgames/{$i->getId()}/delete"><img src="res/icons/computer_delete.png" /></a>
                <a href="admgames/{$i->getId()}/edit"><img src="res/icons/computer_edit.png" /></a>
            </td>
        </tr>
{/foreach}
    </tbody>
</table>
