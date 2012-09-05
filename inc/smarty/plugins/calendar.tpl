<table class="calendar" border="0" cellpadding="1" cellspacing="1">
  <tr>
    <th class="month" colspan="7">
      {$month_name}&nbsp;{$year}
    </th>
  </tr>
  <tr>
    <td class="prev-month" colspan="3">
      <a href="kalender/{$prev_month_end|date_format:$url_format}/{$days_prevmonth}/month">
        {$prev_month_abbrev}
      </a>
    </td>
    <td></td>
    <td class="next-month" colspan="3">
      <a href="kalender/{$next_month_begin|date_format:$url_format}/{$days_nextmonth}/month">
        {$next_month_abbrev}
      </a>
    </td>
  </tr>
  <tr>
  {section name="day_of_week" loop=$day_of_week_abbrevs}
    <th class="day-of-week">{$day_of_week_abbrevs[day_of_week]}</th>
  {/section}
  </tr>
  {section name="row" loop=$calendar}
    <tr>
      {section name="col" loop=$calendar[row]}
        {assign var="date" value=$calendar[row][col]}
        {if $date.time >= $selected_date && $date.time < $endselection}
          <td class="selected-day {if $date.hofe}day-hofe{/if} {if $date.time >= $today_begin && $date.time <= $today_end}day-today{/if}"><a href="kalender/{$date.time|date_format:$url_format}" {if $date.date eq true && $date.red eq true}style="color:red;"{else}style="color:black;"{/if}>{$date.time|date_format:"%e"}</a></td>
        {elseif $date.time|date_format:"%m" == $month}
          <td class="day {if $date.hofe}day-hofe{/if} {if $date.time >= $today_begin && $date.time <= $today_end}day-today{/if}">
            <a href="kalender/{$date.time|date_format:$url_format}" 
			{if $date.date eq true}{if $date.red eq true}style="color:red;"{/if} title="
				{$date.title|truncate:20}
			"{/if}>
              	{$date.time|date_format:"%e"}
            </a>
          </td>
        {else}
          <td class="day"></td>
        {/if}
      {/section}
    </tr>
  {/section}
</table>
