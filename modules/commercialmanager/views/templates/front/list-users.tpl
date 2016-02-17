{* TODO add here the html content, now is in controller. *}
{capture name=path}
<a href="{$link->getPageLink('my-account', true)}">{l s='My account' mod='commercialmanager'}</a>
<span class="navigation-pipe">{$navigationPipe}</span>
{l s='Gestión de cuentas de clientes' mod='commercialmanager'}
{/capture}

{$html}
{strip}
	{addJsDef url_commercial_manager=$url_commercial_manager}
	{addJsDef shop_text_commercial_manager=$shop_text_commercial_manager}
	{addJsDef no_result_commercial_manager=$no_result_commercial_manager}
{/strip}