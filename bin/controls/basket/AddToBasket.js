/**
 * Add to basket button
 *
 * @module package/quiqqer/product-bricks/bin/controls/basket/AddToBasket
 * @author www.pcsg.de (Michael Danielczok)
 */
define('package/quiqqer/product-bricks/bin/controls/basket/AddToBasket', [

    'qui/QUI',
    'Packages',
    'qui/controls/Control',
    'qui/controls/loader/Loader',
    'css!package/quiqqer/product-bricks/bin/controls/basket/AddToBasket.css'

], function (QUI, QUIPackageManager, QUIControl, QUILoader) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/order/bin/frontend/controls/frontendusers/Article',

        Binds: [
            '$addArticleToBasket'
        ],

        initialize: function (options) {
            this.parent(options);

            this.animatable         = false;
            this.buttonWidth        = null;
            this.animationIsRunning = false;
            this.additionInProgress = false;
            this.Label              = null;
            this.Icon               = null;
            this.Loader             = new QUILoader({
                type    : 'fa-refresh',
                cssclass: 'add-to-basket-custom-loader',
                opacity : 0.99
            });

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

            this.animatable  = Elm.getAttribute('data-product-animatable') === '1';
            this.buttonWidth = Elm.getSize().x;

            if (this.animatable) {
                this.Icon = new Element('span', {
                    'class': 'fa fa-check icon-animatable',
                    styles : {
                        position: 'absolute',
                        opacity : 0
                    }
                }).inject(Elm);
            }

            this.Label = Elm.getElement('label');
            this.Loader.inject(Elm);

            this.Loader.show();

            QUIPackageManager.isInstalled('quiqqer/order').then(function (isInstalled) {
                if (!isInstalled) {
                    Elm.destroy();
                    return;
                }

                Elm.addEvent('click', function (event) {
                    event.stop();

                    // one click = add one article
                    if (self.additionInProgress) {
                        return;
                    }

                    self.additionInProgress = true;

                    if (self.animatable && self.animationIsRunning) {
                        return;
                    }

                    if (self.animatable) {
                        self.animationIsRunning = true;
                    }

                    self.$addArticleToBasket(event, productId, Elm);
                });
            });
        },

        /**
         * add article to the basket
         */
        $addArticleToBasket: function (event, productId, Button) {
            var self = this;

            if (typeof event.stop !== 'undefined') {
                event.stop();
            }

            this.Label.setStyle('visibility', 'hidden');
            this.Loader.show();

            require(['package/quiqqer/order/bin/frontend/Basket'], function (Basket) {
                Basket.addProduct(productId).then(function () {
                    self.Loader.hide();

                    if (self.animatable) {
                        self.$animateButton(Button).then(function () {
                            self.additionInProgress = false;
                        });
                    } else {
                        self.Label.setStyle('visibility', 'visible');
                        self.additionInProgress = false;
                    }
                });
            });
        },

        /**
         * Start the button animation
         *
         * @returns {Promise}
         */
        $animateButton: function () {
            var self = this;

            this.Label.setStyle('left', -this.buttonWidth);

            return new Promise(function (resolve) {
                moofx(self.Icon).animate({
                    transform: 'scale(1)',
                    opacity  : 1
                }, {
                    duration: 250,
                    callback: function () {
                        (function () {
                            Promise.all([
                                self.hideIcon(),
                                self.showLabel()
                            ]).then(function () {
                                self.animationIsRunning = false;
                                resolve();
                            });
                        }).delay(750);
                    }
                });
            });
        },

        /**
         * Hide the button icon
         *
         * @returns {Promise}
         */
        hideIcon: function () {
            var self = this;

            return new Promise(function (resolve) {
                moofx(self.Icon).animate({
                    left: '100%'
                }, {
                    duration: 500,
                    callback: function () {
                        self.Icon.setStyles({
                            left     : '',
                            transform: '',
                            opacity  : '0'
                        });
                        resolve();
                    }
                });
            });
        },

        /**
         * Show the button label
         *
         * @returns {Promise}
         */
        showLabel: function () {
            var self = this;
            this.Label.setStyle('visibility', 'visible');

            return new Promise(function (resolve) {
                moofx(self.Label).animate({
                    left: '0px'
                }, {
                    duration: 500,
                    callback: resolve
                });
            });
        }
    });
});
