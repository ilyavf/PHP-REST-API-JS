restfulAPI = function(domain) {
    //todo: check that domain starts with http or https and doesn't ends in a slash /
    this.domain = domain;
    this.authUser = '';
    this.authKey = '';
    this.authSecret = '';

    this.addAuthUser = function(authUser) {
        this.authUser = authUser;
    };

    this.passwordToSecret = function(password) {
        this.authSecret = CryptoJS.SHA512(password).toString();
    };

    this.sign = function(type, endPoint, orderedParams, timestamp) {
        var toSign = type + endPoint + orderedParams + timestamp;
        var hash = CryptoJS.HmacSHA512(toSign, this.authSecret);
        return CryptoJS.enc.Base64.stringify(hash);
    };

    this.addSecondFactor = function(secondFactor) {
        var hash = CryptoJS.HmacSHA512(secondFactor, this.authSecret);
        this.authSecret = CryptoJS.enc.Base64.stringify(hash);
    };

    this.request = function(type, endPoint, paramObj) {
        endPoint = this.domain + endPoint;
        var orderedParamObj = {};
        if (typeof paramObj !== 'undefined') {
            Object.keys(paramObj)
                .sort()
                .forEach(function (v) {
                    orderedParamObj[v] = paramObj[v];
                });
        } else paramObj = {};
        var params = this.obj2Params(orderedParamObj);
        var timestamp = Math.round(new Date().getTime() / 1000);
        var signature = this.sign(type, endPoint, params, timestamp);
        var thisObj = this;
        return this.call(type.toUpperCase(), endPoint, params, signature, timestamp, this.authUser, this.authKey)
            .then(function(xhr) {
                var newSecret = xhr.getResponseHeader('auth-secret');
                if (typeof newSecret !== 'undefined' && newSecret != '') thisObj.authSecret = newSecret;

                return xhr.responseText;
            });
    };

    this.call = function call(type, endPoint, data, signature, timestamp, authUser) {
        return new Promise(function(resolve, reject) {
            var xhr = new XMLHttpRequest();
            var requestTimeout = setTimeout(function() {
                xhr.abort();
                reject({error:"timeout", response: "", xhr: xhr});
            }, 60000);
            xhr.onerror = function() {
                reject({error:xhr.status, response: xhr.responseText, xhr: xhr, type: type, url: endPoint, data: data});
            };
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) return;
                clearTimeout(requestTimeout);
                if (xhr.status !== 200) {
                    reject({error:xhr.status, response: xhr.responseText, xhr: xhr, type: type,  url: endPoint, data: data});
                    return;
                }
                resolve(xhr);
            };
            xhr.open(type, endPoint, true);
            xhr.setRequestHeader('Auth-Timestamp', timestamp);
            xhr.setRequestHeader('Auth-User', authUser);
            xhr.setRequestHeader('Auth-Signature', signature);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            if (data == '' || type != 'POST') {
                xhr.setRequestHeader('Content-type', 'application/json;charset=UTF-8');
                xhr.send();
            } else if (data && type == 'POST') {
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send(data);
            }
        });
    };

    this.obj2Params = function(obj) {
        var str = "";
        for (var key in obj) {
            if (str != "") str += "&";
            str += key + "=" + obj[key];
        }
        return str;
    };
};
