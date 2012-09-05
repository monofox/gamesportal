<title>{$title}</title>
{config_load file="config.ini.php" section="default"}
<base href="{#address#}" />
<link rel="stylesheet" href="res/styles/default.css" type="text/css" />
<link rel="stylesheet" href="res/styles/sidebars.css" type="text/css" />
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<script type="text/javascript">
{literal}
function leeren(elm, restore) {
    if (restore && elm.value == '') {
        elm.value = 'Suchen...';
    } else if (!restore && elm.value == 'Suchen...') {
        elm.value = '';
    }
}
{/literal}
</script>
