({
    baseUrl: './',
    "paths": {
        "jquery": "../../node_modules/jquery/dist/jquery",
        "bootstrap": "../../node_modules/bootstrap/dist/js/bootstrap",
        "bootstrap-select": "../../node_modules/bootstrap-select/dist/js/bootstrap-select",
        "pace": "../../node_modules/pace/pace",

        "can": "../../node_modules/canjs/can",
        "i18n": "../../node_modules/i18n/i18n",
        "text": "../../node_modules/text/text",

        "detect-mobile": "detectmobilebrowser",
        "rest-api": "restfulapi",

        "cryptojs.core": "../../node_modules/cryptojslib/components/core",
        "cryptojs.x64": "../../node_modules/cryptojslib/components/x64-core",
        "cryptojs.sha512": "../../node_modules/cryptojslib/components/sha512",
        "cryptojs.base64": "../../node_modules/cryptojslib/components/enc-base64",
        "cryptojs.hmac": "../../node_modules/cryptojslib/components/hmac",
        "cryptojs.pbkdf2": "../../node_modules/cryptojslib/components/pbkdf2"
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