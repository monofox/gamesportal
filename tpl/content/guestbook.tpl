<fieldset><legend>Eintragen</legend>
    <form action="guestbook/" class="modern_form" method="post">
        <div class="leftbar">
            <label for="gb_name">Name: </label>
        </div>
        <div class="rightbar">
            <input type="text" id="gb_name" name="name" />
        </div>
        <div class="leftbar">
            <label for="gb_comment">Kommentar: </label>
        </div>
        <div class="rightbar">
            <textarea id="gb_comment" name="comment" rows="3" cols="80"></textarea>
        </div>
        <div class="rightbar">
            <input type="hidden" name="create" value="create" />
            <input type="submit" value="Eintragen" />
        </div>
    </form>
</fieldset>

{* now display the entries*}

<div class="commentList">
    <ul>
        {foreach from=$entries key='k' item='i'}
        <li><strong>{$i->name} schrieb am {$i->timestamp} Uhr:</strong><br />
        {$i->comment}
        </li>
        {/foreach}
    </ul>
</div>
