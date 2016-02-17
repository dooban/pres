
<!-- MODULE commercial_manager -->
{if in_array(Configuration::get('PS_COMMERCIALMANAGER_GROUP'),Context::getContext()->customer->getGroups())}


    <li class="lnk_quickorder">
        <a href="{$link->getModuleLink('commercialmanager', 'default', ['process' => 'summary'])|escape:'html'}" title="{l s='List my users' mod='commercialmanager'}">
			<i class="icon-th-list"></i>
            <span>{l s='List my users' mod='commercialmanager'}</span>
        </a>
    </li> 
<!-- END : MODULE commercial_manager -->
{/if}