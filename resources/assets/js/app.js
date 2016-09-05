
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('./sweetalert.min');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the body of the page. From here, you may begin adding components to
 * the application, or feel free to tweak this setup for your needs.
 */

Vue.component('example', require('./components/Example.vue'));

const connectionForm = new Vue({
    el: '#connection_form',
    data: {
        method: '0',
        password: false
    },
    watch: {
        method: function (value) {
            if (value == '2') {
                swal({
                    title: "Important!",
                    text: "Your private key will be stored as a file in our server.\n\nIf you choose to use this authentication method, you agree with storing your keys on our servers.",
                    type: "info",
                    confirmButtonText: "I understand"
                });
            }
        }
    },
    methods: {
        'passwordTyped': function () {
            if (!this.password) {
                this.password = true;
                swal({
                    title: "Important!",
                    text: "Your password will be stored in our database in a human readable form. Deployman will use this password to SSH to your server.\n\nIf you do not want to store your password, leave this field blank.\n\nYou will be asked to type your password every time you create a task involving Deployman connecting to your server.",
                    type: "info",
                    confirmButtonText: "I understand"
                });
            }
        }
    }
});

$('.dropdown-toggle').click(function () {
    $(this).siblings().closest('ul').toggle();
});