define([
    'jquery',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/customer-data'
], function ($, getTotalsAction, customerData) {

    $(document).ready(function(){

        customerData.reload(['cart'], false);

        $(document).on('change', '[data-role="cart-item-qty"]', function(){
            var id = $(this).closest('tbody').data('id-item');
            var val_input = $(this).val();
            postAjaxFormCart(id, val_input);
        });
        var number_click = 1;
        $(document).on('click', '.qty-down-fixed-onclick', function(){
			var val_input = $(this).closest('div.field').find('.control.qty .input-text.qty').val();
            var id = $(this).closest('tbody').data('id-item');
			val_input = parseInt(val_input);
			if(val_input <= number_click){
				val_input = 0;
			}
			else{
				val_input = val_input - number_click;
			}
			$(this).closest('div.field').find('.control.qty .input-text.qty').val(val_input);
            postAjaxFormCart(id, val_input);
			return false;
		});
        $(document).on('click', '.qty-up-fixed-onclick', function(){
			var val_input = $(this).closest('div.field').find('.control.qty .input-text.qty').val();
            var id = $(this).closest('tbody').data('id-item');
			val_input = parseInt(val_input);
			val_input = val_input + number_click;
			$(this).closest('div.field').find('.control.qty .input-text.qty').val(val_input);
            postAjaxFormCart(id, val_input);
			return false;
		});

        function postAjaxFormCart(id, val_input){
            var form = $('form#form-validate');
            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                showLoader: true,
                success: function (res) {
                    var parsedResponse = $.parseHTML(res);
                    var result = $(parsedResponse).find("#form-validate");
                    var sections = ['cart'];

                    $("#form-validate").replaceWith(result);

                    // The mini cart reloading
                    customerData.reload(sections, true);

                    // The totals summary block reloading
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);
                    var item_update = result.find('.item-id-'+id+'');
                    if (item_update) {
                        var qty_item_update = item_update.find('.control.qty .input-text.qty').val();
                        if (qty_item_update != val_input) {
                            window.location.reload();
                        }
                    }
                },
                error: function (xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }
    });
});