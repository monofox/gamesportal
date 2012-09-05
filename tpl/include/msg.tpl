{if isset($succmsg) || isset($errormsg) || isset($infomsg)}
    <div class="modern_form">
        {if isset($succmsg)}
            <div class="succmsg">
                {if is_array($succmsg)}
                    <ul>
                        {foreach from=$succmsg item=i}
                            {if !empty($i)}
                                <li>{$i}</li>
                            {/if}
                        {/foreach}
                    </ul>
                {else}
                    {$succmsg}
                {/if}
            </div>
        {/if}
        {if isset($errormsg)}
            <div class="errormsg">
                {if is_array($errormsg)}
                    <ul>
                        {foreach from=$errormsg item=i}
                            {if !empty($i)}
                                <li>{$i}</li>
                            {/if}
                        {/foreach}
                    </ul>
                {else}
                    {$errormsg}
                {/if}
            </div>
        {/if}
        {if isset($infomsg)}
            <div class="infomsg">
                {if is_array($infomsg)}
                    <ul>
                        {foreach from=$infomsg item=i}
                            {if !empty($i)}
                                <li>{$i}</li>
                            {/if}
                        {/foreach}
                    </ul>
                {else}
                    {$infomsg}
                {/if}
            </div>
        {/if}
    </div>
{/if}
