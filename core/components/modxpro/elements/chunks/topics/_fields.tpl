{foreach $fields as $field => $type}
    {if $field == 'days'}
        <div class="form-group col12 col-md-6">
            <label>{$.en ? 'How many days?' : 'Сколько дней?'}</label>
            <input type="{$type == 'int' ? 'number' : 'text'}" name="days" value="{$days}" placeholder="7">
        </div>
    {elseif $field = 'money'}
        <div class="form-group col12 col-md-6">
            <label>{$.en ? 'How much money, in euros?' : 'Сколько денег, в рублях?'}</label>
            <input type="{$type == 'int' ? 'number' : 'text'}" name="money" value="{$money}" placeholder="10000">
        </div>
    {/if}
{/foreach}