{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if Configuration::get('PAYMENTIN90days_90days') > 0}
<p class="payment_module">
	<a href="{$link->getModuleLink('paymentin90days', 'payment', [], true)|escape:'html'}" title="{l s='Pay by check' mod='paymentin90days'}">
		<img src="{$this_path_payment}paymentin90days.jpg" alt="{l s='Payment in ' mod='paymentin90days'} {Configuration::get('PAYMENTIN90days_90days')} {l s='days.' mod='paymentin90days'}" width="86" height="49" />
		{l s='Payment in ' mod='paymentin90days'} {Configuration::get('PAYMENTIN90days_90days')} {l s='days.' mod='paymentin90days'}
	</a>
</p>
{/if}