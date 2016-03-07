({
    baseUrl: './',
    "paths": {
        "jquery": "../lib/jquery/jquery-2.1.4.min",
        "can": "../lib/canjs/can",
        "bootstrap": "../lib/bootstrap/js/bootstrap",
        "bootstrap-select": "../lib/bootstrap-select/js/bootstrap-select",
        "pace": "../lib/pace/pace.min",
        "i18n": "../lib/i18n/i18n",
        "detect-mobile": "../lib/detectmobile/detectmobilebrowser",
        "text": "../lib/text/text",
        "rest-api": "../lib/rest-api-js/restfulapi",
        "cryptojs.core": "../lib/rest-api-js/core",
        "cryptojs.x64": "../lib/rest-api-js/x64-core",
        "cryptojs.sha512": "../lib/rest-api-js/sha512",
        "cryptojs.base64": "../lib/rest-api-js/enc-base64",
        "cryptojs.hmac": "../lib/rest-api-js/hmac",
        "cryptojs.pbkdf2": "../lib/rest-api-js/pbkdf2"
    },
    "shim": {
        "bootstrap": {
            "deps": ["jquery"]
        },
        "bootstrap-select": {
            "deps": ["jquery", "bootstrap"]
        },
        "cryptojs.core": {
            "exports": "CryptoJS"
        },
        "cryptojs.x64": {
            "deps": ["cryptojs.core"],
            "exports": "CryptoJS.x64"
        },
        "cryptojs.sha512": {
            "deps": ["cryptojs.core", "cryptojs.x64"],
            "exports": "CryptoJS.sha512"
        },
        "cryptojs.base64": {
            "deps": ["cryptojs.core"],
            "exports": "CryptoJS.base64"
        },
        "cryptojs.hmac": {
            "deps": ["cryptojs.core"],
            "exports": "CryptoJS.hmac"
        },
        "cryptojs.pbkdf2": {
            "deps": ["cryptojs.core", "cryptojs.hmac", "cryptojs.sha512"],
            "exports": "CryptoJS.pbkdf2"
        }
    },
    name: "app",
    out: "app-built.js"
})