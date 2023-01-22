const {Component} = Shopware;
import template from './bunnycdn-alert.html.twig';

Component.extend('bunnycdn-alert', 'sw-alert', {
    template,

    props: {
        message: {
            type: String,
            required: false,
            default: ''
        }
    },
})
