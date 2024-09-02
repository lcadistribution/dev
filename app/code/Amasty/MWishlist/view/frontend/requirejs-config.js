var config = {
    config: {
        mixins: {
            'Magento_Wishlist/js/product/addtowishlist-button': {
                'Amasty_MWishlist/js/mixin/addtowishlist-button-mixin': true
            }
        }
    },
    map: {
        '*': {
            'amwishlistQty': 'Amasty_MWishlist/js/components/qty',
            'amPageTabs': 'Amasty_MWishlist/js/components/page/tabs',
            'amListName': 'Amasty_MWishlist/js/action/list-name',
            'amWishlistSearch': 'Amasty_MWishlist/js/components/search',
            'Magento_Wishlist/template/product/addtowishlist-button.html': 'Amasty_MWishlist/template/product/addtowishlist-button.html'
        }
    }
};
