require([
    'jquery',
    'mage/url'
], function ($,url) {
    $(document).ready(function(){
        $(".askprice").click(function(){
            var urlPath = url.build('md_customerprice/index/customerprice');
            var pId = $(this).attr('id');
            var button = $(this);
            $.ajax({
                showLoader: true,
                url: urlPath,
                type: "POST",
                data:{
                    product_id: pId,
                },
                dataType: "json",
                success: function (data) {
                    if (data.status) {
                        if($('body').hasClass('md_customerprice-index-offers')){
                            $(button).prev().html(data.message);
                        }else{
                            $("#product-item-info_"+pId+" .price-box").html(data.message);
                        }
                        $(button).hide();
                    } 
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });
    });
});