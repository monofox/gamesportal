<!DOCTYPE html>
<html>
    <head>
        {include file="header.tpl"}
    </head>
    <body>
        <a name="top" id="top" href="#"> </a>
        <div id="world" class="world">
            <div id="header" class="header">
                <noscript>
                    <div id="headerNO" class="headerNO">
                        Bitte beachten Sie, dass diese Seite Javascript und Cookies benötigt.
                    </div>
                </noscript>
            </div>

            <div id="banner" class="banner">
                    <div id="banner_option" style="float: left; text-align: right;">
                        <a href="{#address#}">
                            <img src="res/logo.png" alt="Zockerportal" border="0" style="margin: 0.3em 0.7em 0 0.7em;"/>
                        </a>
                    </div>
                    <div class="left">
                        <a href="{#address#}">
                            <span class="main_text">Zockerportal</span>
                            <span class="sub_text">für ganz Harte</span>
                        </a>
                    </div>
            </div>


            <div id="menu" class="menu">
                <div id="menu_list">
                    <div class="menu_main_point">
                        <a href="home" class="menu_item {if $content.mnu eq "home"}menu_selected{/if}">Startseite</a>
                    </div>
                    <div class="menu_item_split"></div>
                    <div class="menu_main_point">
                        <a href="games" class="menu_item {if $content.mnu eq "game"}menu_selected{/if}">Spiele</a>
                    </div>
                    <div class="menu_item_split"></div>
            </div>
        </div>
        <div id="info-bar">
            <div id="search" class="search">
                <form id="form_hbar_menu_search" method="get" action="search"
                      onsubmit="changeWhiteSpace('form_hbar_menu_search_q');return true;">
                    <fieldset>
                        <input id="form_hbar_menu_search_q" type="text" name="q" 
                               onfocus="leeren(this, false)" onblur="leeren(this, true)"
                               value="{if isset($keywords)}{$keywords}{else}Suchen...{/if}" />
                        <button type="submit">&nbsp;</button>
                    </fieldset>
                </form>
            </div>
        </div>

        <div class="sideBar" id="sidebar_left">
        {if isset($loggedin) && $loggedin}
            <div class="box">
                <div class="handler"></div>
                <div class="box_header">
                    <img alt="Login-Box" src="res/ico/user.png" />
                    <div class="box_title">Sie sind angemeldet</div>
                </div>
                <div class="box_content">
                    <div class="defaultTpl">
                        Willkommen {$userFirstName} {$userLastName}!<br/><br/>
                        <a href="user/logout">Abmelden</a>
                    </div>
                </div>
                <div class="box_footer"></div>
            </div>
        {else}
            <div class="box">
                <div class="handler"></div>
                <div class="box_header">
                    <img alt="Login-Box" src="res/ico/user.png" />
                    <div class="box_title">Anmelden</div>
                </div>
                <div class="box_content">
                    <div class="defaultTpl">
                        <p>Melden Sie sich mit Ihren persönlichen Daten
                        ein, um kommentieren zu können: </p>
                        <form action="user/login" class="userForm" accept-charset="utf-8" method="post" name="ulogin">
                            <label>Benutzername: <input type="text" name="login_name"/><br/>
                            <label>Kennwort: <input type="password" name="login_pass"/><br /><br />
                            <input type="submit" value="Anmelden" />
                        </form>
                    </div>
                </div>
                <div class="box_footer"></div>
            </div>
        {/if}
        </div>

        <div id="content" class="content general_content">
            <h2 class="content_heading">{$content.heading}</h2>
            {*If there are some Messages - show it here!*}
            {include file="include/msg.tpl"}
            {if $content.tpl ne ""}
            {include file=$content.tpl}
            {/if}
        </div>


        <div class="footer">
            <div>Copyright &copy; 2012 bei mir. Manche Rechte vorbehalten. Design: <a href="http://fls-wiesbaden.de">FLS-Wiesbaden</a></div>
            {*<div>{$conf->contact->name} - {$conf->contact->street} - {$conf->contact->postalCode}
            {$conf->contact->city} - {$conf->contact->phone}*}
            {if $debug}
                <!-- Seite erstellt in {$runtime|string_format:"%.4f"} ms {if $loggedin eq true}mit {$sql_queries} Datenbankabfragen{/if} --></div>
                {*Only if debug is active...*}
                <!-- Peak memory usage in MB: {$peakMemory} -->
            {/if}
        </div>
    </div> <!-- ENDE div:world -->
</body>
</html>
