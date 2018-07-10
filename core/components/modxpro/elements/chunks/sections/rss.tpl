<form>
    <table class="table">
        {foreach $results as $item}
            <tr>
                <th class="col-10 no-gutters">
                    <label for="{$item.uri}" class="col-12">{$item.pagetitle}</label>
                </th>
                <td class="col-2">
                    <input type="checkbox" name="{$item.uri}" value="1" id="{$item.uri}"/>
                </td>
            </tr>
        {/foreach}
    </table>
</form>