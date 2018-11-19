(function() {
	'use strict';

    httpService.$inject = ['$http'];

	function httpService($http) {

        function _get(url, callback) {

            //$http.defaults.headers.common['Authorization'] = $cookies.get('PHPSESSIONID');

            $http.get(url)
                .success(function(data) {
                    if (callback != undefined) {
                        callback(data);
                    } 
                })
                .error(function(data, status, headers, config) {
                    if (callback != undefined) {
                        callback({ error :  true, message: "Network error => data:"+data+" headers:"+headers});
                    }      
                });
        }

        return {
            get: _get
        };
    }

    angular
        .module('atpl')
        .factory('httpService', httpService);
})();