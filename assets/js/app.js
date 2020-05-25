/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
// External Libraries
import '../styles/external/bootstrap-datepicker3.min.css';
import '../styles/external/bootstrap-datepicker3.standalone.min.css';
import './external/datatables/dataTables.bootstrap4.css';
// Custom CSS
import '../styles/app.scss';

import 'sweetalert2/src/sweetalert2.scss';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';
// import 'mdbootstrap/js/mdb';
import moment from 'moment';

import { easeOutQuad } from './external/jquery-easing/jquery.easing';
import { dataTablesjQuery } from './external/datatables/jquery.dataTables';
import { dataTablesBootstrap } from './external/datatables/dataTables.bootstrap4';
import './external/clockpicker/src/clockpicker';

import LoggerUtil from "./utils/LoggerUtil";
import * as helpers from "./utils/Helpers";
import * as isEmpty from "is-empty";
import Swal from 'sweetalert2/dist/sweetalert2.js';
import Chart from 'chart.js';

import bsCustomFileInput from 'bs-custom-file-input';

import "./main";

window.logger = LoggerUtil();
window.helpers = helpers;
window.isEmpty = isEmpty;
window.moment = moment;
window.Swal = Swal;
window.Chart = Chart;

// Define my name keys session storage
window.keys_sessions = {
    SHOP_SELECT: '__admin_shop_select',
    PRODUCT_SELECT: '__admin_product_select',
    SHOP_CREATE: '__admin_shop_create',
    IMAGE_STORE_UPLOAD: '__image_store_upload',
};

// Endpoint API

window.endpoints = {
    IMGUR_UPLOAD: 'https://api.imgur.com/3/image'
};

window.keys_sessions.fn = {
    SHOP_SELECT_INTEGRITY: (forceClear = false) => {
        try {
            if (!JSON.parse(sessionStorage.getItem(window.keys_sessions.SHOP_SELECT)) || forceClear) {
                sessionStorage.setItem(window.keys_sessions.SHOP_SELECT, "{}");
            }
        }catch (e) {
            window.logger.error("Error encountered during parse data in session [SHOP_SELECT_INTEGRITY] ! Go regenerate ...");
            sessionStorage.setItem(window.keys_sessions.SHOP_SELECT, "{}");
        }
    },
    PRODUCT_SELECT_INTEGRITY: (forceClear = false) => {
        try {
            if (!JSON.parse(sessionStorage.getItem(window.keys_sessions.PRODUCT_SELECT)) || forceClear) {
                sessionStorage.setItem(window.keys_sessions.PRODUCT_SELECT, "{}");
            }
        }catch (e) {
            window.logger.error("Error encountered during parse data in session [PRODUCT_SELECT_INTEGRITY] ! Go regenerate ...");
            sessionStorage.setItem(window.keys_sessions.PRODUCT_SELECT, "{}");
        }
    },
    SHOP_CREATE_INTEGRITY: (forceClear = false) => {
        try {
            if (!JSON.parse(sessionStorage.getItem(window.keys_sessions.SHOP_CREATE)) || forceClear) {
                sessionStorage.setItem(window.keys_sessions.SHOP_CREATE, JSON.stringify({ horaires: [] }));
            }
        }catch (e) {
            window.logger.error("Error encountered during parse data in session [SHOP_CREATE_INTEGRITY] ! Go regenerate ...");
            sessionStorage.setItem(window.keys_sessions.SHOP_CREATE, JSON.stringify({ horaires: [] }));
        }
    },
    IMAGE_STORE_UPLOAD: (forceClear = false) => {
        try {
            if (!JSON.parse(localStorage.getItem(window.keys_sessions.IMAGE_STORE_UPLOAD)) || forceClear) {
                localStorage.setItem(window.keys_sessions.IMAGE_STORE_UPLOAD, JSON.stringify({ images: [] }));
            }
        }catch (e) {
            window.logger.error("Error encountered during parse data in session [IMAGE_STORE_UPLOAD] ! Go regenerate ...");
            localStorage.setItem(window.keys_sessions.IMAGE_STORE_UPLOAD, JSON.stringify({ images: [] }));
        }
    },
};

(function () {
    'use strict';

    window.logger.log('Hello Webpack Encore! Edit me in assets/js/app.js');

    // Default configuration Chart.js
    window.Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    window.Chart.defaults.global.defaultFontColor = '#858796';


    // INIT bsCustomFileInput
    bsCustomFileInput.init();


    /**
     * Fix js fail load with webpack encore
     */
    easeOutQuad($);
    // Attention à l'ordre de chargement des scripts pour datatables c'est le jquery ensuite bootstrap
    dataTablesjQuery($, window, document);
    dataTablesBootstrap($, window, document);


    // DATAPICKER HANDLE
    let date = new Date();

    let defaultOptions = {
        format: 'dd/mm/yyyy',
        clearBtn: true,
        autoclose: true,
        language: 'fr',
        toggleActive: true
    };

    $('.datepicker').datepicker({
        ...defaultOptions,
        endDate: new Date(date.setFullYear(date.getFullYear() - 10)),
    });

    $('.datepicker-shippping').datepicker({
        ...defaultOptions,
        startDate: new Date(),
        //endDate: new Date(date.setFullYear(date.getFullYear() + 2)),
        todayBtn: true,
        todayHighlight: true,
    });

    for (let conf of [
        {
            selector: 'hOuvertureMatin',
            option: {}
        },
        {
            selector: 'hFermetureMatin',
            option: {}
        },
        {
            selector: 'hOuvertureMidi',
            option: {}
        },
        {
            selector: 'hFermetureMidi',
            option: {}
        },
    ]) {
        $(`#horaire_magasin_${conf.selector}`).clockpicker({
            ...conf.option,
            autoclose: true
        });
    }

    // Define my function
    function timeNorm(timestamp) {
        return timestamp ? moment.utc(timestamp * 1000).format('HH:mm') : '';
    }

    window.buildRowHoraire = (pHoraire) => {
        return `<div data-id="${pHoraire.id}" class="list-group-item list-group-item-action d-flex" href="#list-item-4" type="text-overflow: ellipsis;">
            <div class="w-25">
                ${pHoraire.jour}
            </div>
            <div class="d-flex w-100 ml-2 pl-2 w-75" style="border-left: 1px solid black">
                <div class="w-100">
                    <small class="font-weight-bolder">Matin</small>
                    <div>
                        ${timeNorm(pHoraire.hOuvertureMatin.timestamp)} -
                        ${timeNorm(pHoraire.hOuvertureMidi.timestamp)}
                    </div>
                </div>
                <div class="w-100">
                    <small class="font-weight-bolder">Après-Midi</small>
                    <div>
                        ${timeNorm(pHoraire.hFermetureMatin.timestamp)} -
                        ${timeNorm(pHoraire.hFermetureMidi.timestamp)}
                    </div>
                </div>
            </div>
            <button data-id="${pHoraire.id}" class="btn dismiss-horaire-magasin mr-2" href="#" >
                <i class="fas fa-minus-square fa-sm fa-fw text-gray-400"></i>
            </button>
        </div>`;
    };

    window.showImage = (e, url, noSupport, selector = null) => {
        if (noSupport) {
            return window.helpers.pingAlertTemporary(null, {
                elevate: true,
                message: "Ohh ! Attention vous venez d'envoyez votre image en fichier, dommage ce site le supporte pas encore cette fonctonnalité !",
                type: 'warning'
            });
        }
        (!isEmpty(selector) ? selector : $('#PreviewImage')).css({
            backgroundImage: `url('${url}')`
        });
    };

})($);