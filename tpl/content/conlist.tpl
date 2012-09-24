<h3>Konsole wählen</h3>
<ul class="gameList">
{foreach from=$platforms key="k" item="i"}
<li class="gameEntry"><a href="popgames/{$i->platID}">{$i->platName}</a></li>
{/foreach}
</ul>
