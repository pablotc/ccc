{capture name=path}{l s='Shipping'}{/capture}
<h2>{l s='Order summary' mod='DPay'}</h2>

{assign var='current_step' value='payment'}

<h3>{l s='Payment Card Details' mod='DPay'}</h3>

<form action="{$this_path_ssl}validation.php" method="post">
	<table border="0">

		<tr>

			<td>

				{l s='Name on Card:' mod='DPay'}
			</td>
			<td>
				<input type="text" name="cardholderName" id="cardholderName" value="{$cardholderName}"/>

			</td>

		</tr>

		<tr>
			<td>
				{l s='Credit Card Number:' mod='DPay'}
			</td>
			<td>
				<input type="text" name="cardNumber" id="cardNumber" value="{$cardNumber}" />
			</td>
		</tr>
		<tr>
			<td>
				{l s='Expiration Date:' mod='DPay'}
				<div id="errExpDate" style="color:red;{if $errExpDate eq '1'}display: block;{else}display: none;{/if}">{l s="Valid Expiration Date is Required" mod="creditcard"}</div>
			</td>
			<td>
				{html_select_date 
					prefix='expDate_' 
					start_year='-0'
   					end_year='+15' 
					display_days=false
					year_empty="Year" 
					month_empty="Month"}
			</td>
		</tr>
	</table>

<p class="cart_navigation">
	<a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='DPay'}</a>
	<input type="submit" name="paymentSubmit" value="{l s='Submit Order' mod='creditcard'}" class="exclusive_large" />
</p>
</form>