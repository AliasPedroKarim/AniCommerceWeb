import LoggerUtil from "./LoggerUtil";

let logger = LoggerUtil('Helpers');

export let getRandomInt = (max) => {
    return Math.floor(Math.random() * Math.floor(max));
};

/**
 *
 * @param {jQuery.Object} selector
 * @param {message?: string,type?: string, delay?: number,elevate?: false, css?: CSSStyleDeclaration, dismissible?: boolean, shadowDp?: number} options
 */
export let pingAlertTemporary = (selector, options = { }) => {
    let randomID = 'TotoTemporary' +  getRandomInt(9999999999);
    let timeFadeOut = 1600;

    options = {
        message: options.message ? options.message : 'Bravo ! toto...',
        type: options.type ? options.type : 'info',
        delay: options.delay ? options.delay : 15,
        elevate: options.elevate ? options.elevate : false,
        dismissible: options.dismissible ? options.dismissible : true,
        css: options.css ? options.css : {  },

        // Show file assets/styles/utils/_shadow.scss, for more information...
        shadowDp: options.shadowDp ? options.shadowDp : 4
    };

    logger.log(`Summon Alert Element Temporary name ID is (${randomID})`);

    let initial = 10;
    $(`div.toto-temporary-elevate[id!="${randomID}"]`).each((key, row) => {
        initial += row.clientHeight + 10;
    });

    $(options.elevate ? 'body' : selector).append(`
            <div id="${randomID}" class="toto-temporary ${options.elevate ? 'toto-temporary-elevate' : selector} alert alert-${options.type} ${options.dismissible ? 'alert-dismissible' : ''} fade show shadow-${options.shadowDp}dp" role="alert">
                ${options.message}
                ${options.dismissible ? 
                    `<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>` : ''}
            </div>`.trim());

    let h;
    function handleposition() {
        initial = 10;
        $(`div.toto-temporary-elevate[id!="${randomID}"]`).each((key, row) => {
            if (row.style.top < $(`#${randomID}`)[0].style.top) {
                initial += row.clientHeight + 10;
            }
        });

        // console.log(randomID, initial)

        $(`#${randomID}`).css({
            top: initial,
        });
        h = requestAnimationFrame(handleposition);
    }

    h = requestAnimationFrame(handleposition);

    if (options.elevate) {

        $(`#${randomID}`).css({
            ...options.css,
            position: 'fixed',
            zIndex: 99999,
            right: 10,
            transition: '.8s'
        });
    }

    let time = setTimeout(function () {
        $(`#${randomID}`).fadeOut({
            delay: timeFadeOut,
            easing: "linear",
            complete: () => {
                cancelAnimationFrame(h);
                document.getElementById(randomID).remove();
            },
            progress: (animation, progress, remainingMs) => {
                // logger.log(animation, progress, remainingMs)
            }
        });
        /*$(randomID).remove();*/
        clearTimeout(time)
    }, options.delay * 1000);
};

export let isUrl = (url) => {
    return !!(url.match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g));
};

export let getExtensionWithFilename = (filename) => {
    return /(?:\.([^.]+))?$/.exec(filename)[1];
};

export let updateQueryStringParameter = (uri, key, value) => {
    let re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    let separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
        return uri + separator + key + "=" + value;
    }
};

export let number_format = (number, decimals, dec_point, thousands_sep) => {
    // *     example: number_format(1234.56, 2, ',', ' ');
    // *     return: '1 234,56'
    number = (number + '').replace(',', '').replace(' ', '');
    let n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
            let k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
};
