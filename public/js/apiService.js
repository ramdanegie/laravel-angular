var apiService = angular.module('apiService', []);
apiService.service('apiService', [ '$q', '$http',
    function ( $q, $http) {
        return {
            get: function (obj) {

                var deffer = $q.defer();
                if (obj.method === undefined)
                    obj.method = "GET";
                var authorization = ""// "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJzdXN0ZXIifQ.N9hHxNwWtiKvGYpzaquS8PqFJ8E5yYVKIb48GoP4jQgowbKYJaUvSdSRdSqia-2VJyiwwatpJ7E-zleqcho2ng";
                var arr = document.cookie.split(';')
                for (var i = 0; i < arr.length; i++) {
                    var element = arr[i].split('=');
                    if (element[0].indexOf('authorization') > -1) {
                        authorization = element[1];
                    }
                }
                var url = "";
                if (obj.url.indexOf("?") >= 0) {
                    url = obj.url;
                } else
                    url = obj.url;

                $http.get(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-AUTH-TOKEN': authorization
                    }
                }).then(function successCallback(response) {
                    response.statResponse = true;
                    deffer.resolve(response);
                }, function errorCallback(response) {
                    if (response.data == null)
                        window.messageContainer.error("Maaf, Terjadi kesalahan saat memproses data");
                    response.statResponse = false;
                    deffer.resolve(response);
                });
                return deffer.promise;
            },
            post: function (obj, data) {
                // console.log(JSON.stringify(data));
                var deffer = $q.defer();
                var authorization = ""//"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJzdXN0ZXIifQ.N9hHxNwWtiKvGYpzaquS8PqFJ8E5yYVKIb48GoP4jQgowbKYJaUvSdSRdSqia-2VJyiwwatpJ7E-zleqcho2ng";
                var arr = document.cookie.split(';')
                for (var i = 0; i < arr.length; i++) {
                    var element = arr[i].split('=');
                    if (element[0].indexOf('authorization') > -1) {
                        authorization = element[1];
                    }
                }
                var url = "";
                if (obj.url.indexOf("?") >= 0) {
                    url = obj.url;
                } else
                    url = obj.url;
                var req = {
                    method: 'POST',
                    url: url,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-AUTH-TOKEN': authorization
                    },
                    data: data
                }
                $http(req).then(function successCallback(response, a, b) {
                    /*var msg = response.headers("x-message");
                    window.messageContainer.log(msg);*/

                    var msg = response.data.messages;
                    window.messageContainer.log(msg);

                    deffer.resolve(response);
                }, function errorCallback(response) {
                    //var msgError = response.headers("x-message");

                    if (response.data != null) {
                        var msgError = response.data.messages;

                        if (msgError != "") {
                            var p = response.data.errors

                            for (var key in p) {
                                if (p.hasOwnProperty(key)) {
                                    for (var i = 0; i < p[key].length; i++) {
                                        window.messageContainer.error(key + " : " + p[key][i])
                                    }
                                }
                            }

                            window.messageContainer.error(msgError);
                        }
                    } else {
                        window.messageContainer.error("Maaf, halaman API tidak ditemukan");
                    }

                    deffer.reject(response);

                });
                return deffer.promise;
            },
        }
    }])
