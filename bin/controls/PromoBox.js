/**
 * @module package/quiqqer/product-bricks/bin/controls/PromoBox
 * @author www.pcsg.de (Michael Danielczok)
 */
define('package/quiqqer/product-bricks/bin/controls/PromoBox', [

    'qui/QUI',
    'qui/controls/Control',
    'Locale'

], function (QUI, QUIControl, QUILocale) {
    "use strict";

    var lg = 'quiqqer/bricks';

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/product-bricks/bin/controls/PromoBox',

        Binds: [],

        options: {
            url   : false,
            target: '_blank'
        },

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * @event on import
         */
        $onImport: function () {

            var Box = this.$Elm.getElement('.quiqqer-productbricks-promobox-wrapper');

            if (this.getAttribute('url')) {
                Box.style.cursor = 'pointer';
//                Box.title = this.getAttribute('url');
                Box.addEvent('click', function () {
                    window.open(this.getAttribute('url'), this.getAttribute('target'));
                }.bind(this));
            }
        }
    });
});

