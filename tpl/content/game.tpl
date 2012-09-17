<div class="game">
<div class="gameImage left">
    {if !is_null($game->getCover())}
    <img src="cover/{$game->getCover()->getId()}-{$game->getCover()->getName()}" width="200" />
    <p>&nbsp;</p>
    {/if}
</div>
<div class="gameDetail">
<p class="platforms"><strong>Plattform: </strong>
{foreach from=$game->getPlatforms() key='k' item='i'}{if $i@index > 0}, {/if}{$i->getName()}{/foreach}
</p>
{if !is_null($game->getUSK())}
<p class="usk"><strong>USK-Einstufung:</strong> USK ab {$game->getUSK()}</p>
{/if}
{if count($game->getLanguages()) > 0}
    <p class="language"><strong>Sprachen: </strong>
    {foreach from=$game->getLanguages() key='k' item='i'} 
    {if $i@index > 1}, {/if}{$i->getText()}
    {/foreach}
    </p>
{/if}
{if count($game->getCompats()) > 0}
    <p class="platforms"><strong>Kompatibel mit: </strong>
    {foreach from=$game->getCompats() key='k' item='i'}{if $i@index > 0}, {/if}{$i->getName()}{/foreach}
    </p>
{/if}
{if $game->getFeatures() ne ""}
    <p class="features"><strong>Features: </strong>{$game->getFeatures()}</p>
{/if}
<p class="description">
    {$game->getDescription()}
</p> 
<div class="prices"><strong>Preis: </strong>
{foreach from=$game->getPlatforms() key='k' item='i'}
{if $i->getPrice() > 0}{if $i@index > 0}, {/if}{$i->getPrice()}&euro; ({$i->getName()}){/if}
{/foreach}
</div>
<div class="rating">
<p>
{section name="ratingLoop" start='1' step='1' loop='6'}
{if $smarty.section.ratingLoop.index <= floor($rating)}
<img src="res/ico/star_full.png" width="25" height="25" />
{elseif $smarty.section.ratingLoop.index eq ceil($rating) && (floor($rating)) < $smarty.section.ratingLoop.index}
<img src="res/ico/star_half.png" width="25" height="25" />
{else}
<img src="res/ico/star_empty.png" width="25" height="25" />
{/if}
{/section}
</p>
</div>
</div>
<p>&nbsp;</p>
</div>
