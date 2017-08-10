{if $isRecurring}
    <button class="small" onClick="javascript:showPMW{$productID}('{lang}wcf.payment.pmw.button.subscription{/lang}');">{lang}wcf.payment.pmw.button.subscription{/lang}</button>
{else}
    <button class="small" onClick="javascript:showPMW{$productID}('{lang}wcf.payment.pmw.button.purchase{/lang}');">{lang}wcf.payment.pmw.button.purchase{/lang}</button>
{/if}

<script data-relocate="true">
    function showPMW{$productID}(pmwTitle) {
        if (!$('#product_{$productID}').hasClass('pmwInitiated')) {
            $('#product_{$productID}').html('{@$widget|encodeJS}');
            $('#product_{$productID}').addClass('pmwInitiated');
            $('#product_{$productID}').removeClass('invisible');
        }
        $('#product_{$productID}').wcfDialog({literal}{title: pmwTitle}{/literal});
    }
</script>

<div id="product_{$productID}" class="invisible" style="text-align: center;"></div>