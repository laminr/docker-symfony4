(function () {
    'use strict';

    angular
        .module("atpl", ['ngResource', 'ngCookies', 'ngSanitize'])
        .factory('httpRequestInterceptor', ['$cookies', function ($cookies) {
            return {
                request: function (config) {
                    var cookie = $cookies.get('PHPSESSID');
                    // use this to destroying other existing headers
                    config.headers = {'PHPSESSID': cookie};

                    // use this to prevent destroying other existing headers
                    // config.headers['Authorization'] = 'authentication';

                    return config;
                }
            }
        }])
    // .config(
    // 	function($interpolateProvider){
    // 		$interpolateProvider.startSymbol('[[').endSymbol(']]');
    // 	}
    // )
    ;
})();