/**
 * Add to basket button
 *
 * @module package/quiqqer/product-bricks/bin/controls/basket/AddToBasket
 * @author www.pcsg.de (Michael Danielczok)
 */
define('package/quiqqer/product-bricks/bin/controls/basket/AddToBasket', [

    'qui/QUI',
    'qui/controls/Control',
    'package/quiqqer/order/bin/frontend/Basket',
    'css!package/quiqqer/product-bricks/bin/controls/basket/AddToBasket.css'

], function (QUI, QUIControl, Basket) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/order/bin/frontend/controls/frontendusers/Article',

        Binds: [
            '$addArticleToBasket'
        ],

        initialize: function (options) {
            this.parent(options);

            this.animatable = false;
            this.buttonWidth = null;
            this.animationIsRunning = false;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event: on import
         */
        $onImport: function () {
            var Elm       = this.getElm(),
                self      = this,
                productId = Elm.getAttribute('data-product-id');

            this.animatable = Elm.getAttribute('data-product-animatable') === '1';
            this.buttonWidth = Elm.getSize().x;

            Elm.addEvent('click', function (event) {
                event.stop();
                if (self.animationIsRunning) {
                    return;
                }

                self.animationIsRunning = true;
                self.$addArticleToBasket(productId, Elm)
            })
        },

        /**
         * add article to the basket
         */
        $addArticleToBasket: function (productId, Button) {
            if (typeof event.stop !== 'undefined') {
                event.stop();
            }

            Basket.addProduct(productId);

            if (this.animatable) {
                this.$animateButton(Button);
            }
        },

        /**
         * Start the button animation
         *
         * @param Button
         */
        $animateButton: function (Button) {
            var self  = this,
                label = Button.getElement('label'),
                icon  = Button.getElement('.fa');

            // work around. not good.
            moofx(label).animate({
                left: -this.buttonWidth
            }, {
                duration: 1
            });


            moofx(icon).animate({
                transform: 'scale(1)',
                opacity  : 1
            }, {
                duration: 250,
                callback: function () {
                    (function () {
                        self.hideIcon(icon);
                        self.showLabel(label);
                        self.animationIsRunning = false;
                    }).delay(500);
                }
            })
        },

        /**
         * Hide the button icon
         *
         * @param icon
         */
        hideIcon: function (icon) {
            moofx(icon).animate({
                left: '100%'
            }, {
                callback: function () {
                    icon.setStyles({
                        left     : '',
                        transform: '',
                        opacity  : '0'
                    })
                }
            });
        },

        /**
         * Show the button label
         *
         * @param label
         */
        showLabel: function (label) {
            moofx(label).animate({
                left: '0px'
            }, {
                duration: 500
            })
        }
    });
});