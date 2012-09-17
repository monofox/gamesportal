<h3>Konsole wählen</h3>
<ul class="gameList">
{foreach from=$console key="k" item="i"}
<li class="gameEntry"><a href="popgamescon/{$i->platId()}">{$i->platName()}</a></li>
{/foreach}
</ul>
