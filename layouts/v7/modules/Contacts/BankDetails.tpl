{if $selected_bank}
    {assign var=iban value=$selected_bank->getIban()|lower|replace:' ':''}
    {assign var=bank_routing_no value=$selected_bank->get('bank_routing_no')|lower|replace:' ':''}

    {if isset($selected_bank) && $selected_bank && method_exists($selected_bank, 'getId')}
        <input type="hidden" class="selected-bank" value="{$selected_bank->getId()}">
    {/if}

    <div>
        Please transfer the payment net of charges to our bank account:<br>
        Beneficiary: {$selected_bank->get('beneficiary_name')}<br>
        Account No: {$selected_bank->get('account_no')}<br>
        Account Currency: {$selected_bank->get('account_currency')}<br>

        {if $iban neq 'x'}
            IBAN: {$selected_bank->getIban()}<br>
        {/if}

        Bank: {$selected_bank->get('bank_name')}<br>
        Bank Address: {$selected_bank->get('bank_address')}<br>
        Swift Code: {$selected_bank->get('swift_code')}<br>

        {if $bank_routing_no neq 'x'}
            Bank Code: {$selected_bank->get('bank_code')}<br>
            Branch Code: {$selected_bank->get('branch_code')}<br>
        {else}
            Routing No: {$selected_bank->get('bank_routing_no')}<br>
        {/if}

        <br><br>

        {if !empty($selected_bank->get('intermediary_bank'))}
            Intermediary Bank: {$selected_bank->get('intermediary_bank')}<br>
            Swift Code: {$selected_bank->get('intermediary_swift_code')}<br>
        {/if}
    </div>
{/if}
