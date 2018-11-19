(function(){
    'use strict';

    function VytaLoader() {

        function AbkmStepLoaderCtrl() {
            var vm = this;
        }

        return {
            restrict: 'E',
            templateUrl: path.asset.js + '/ngApp/directive/loader/loader.view.html',
            controllerAs: 'vm',
            controller: AbkmStepLoaderCtrl,
            bindToController: true
        };
    }

    angular
        .module('atpl')
        .directive('loader', VytaLoader);

})();