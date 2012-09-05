<ul class="gameList">
{foreach from=$games key="k" item="i"}
<li class="gameEntry"><a href="game/{$i->getId()}-{$i->getShortName()}">{$i->getTitle()}</a></li>
{/foreach}
</ul>
