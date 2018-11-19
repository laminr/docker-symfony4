(function () {
    'use strict';

    qSelector.$inject = ['httpService'];

    function qSelector(httpService) {

        function qSelectorController() {

            var ctrl = this;
            ctrl.tags = [];
            ctrl.tag = "";

            ctrl.type = {
                source: {name: "source", target: "?target=source"},
                subject: {name: "subject", target: "?target=subject"},
                topic: {name: "topic", target: "?target=topic"}
            };

            ctrl.filters = {
                sources: [],
                subjects: [],
                topics: []
            };

            ctrl.data = {
                source: null,
                subject: null,
                topic: null

            };

            function getOptions(type) {

                var url = ctrl.filter + type.target;
                var go = false;
                switch (type) {
                    case ctrl.type.source:
                        go = true;
                        break;
                    case ctrl.type.subject:
                        if (ctrl.data.source != null) {
                            url += "&id=" + ctrl.data.source.id;
                            go = true;
                        }
                        break;
                    case ctrl.type.topic:
                        if (ctrl.data.subject != null) {
                            url += "&id=" + ctrl.data.subject.id;
                            // TAG
                            go = true;
                        }
                        break;
                }

                if (go) {
                    console.log(url);
                    httpService.get(url, function (response) {
                        switch (type) {
                            case ctrl.type.source:
                                ctrl.filters.sources = response.filters;
                                break;
                            case ctrl.type.subject:
                                if (response.error === undefined)
                                    ctrl.filters.subjects = response.filters;
                                break;
                            case ctrl.type.topic:
                                if (response.error === undefined)
                                    ctrl.filters.topics = response.filters;
                                break;
                        }

                        ctrl.tags = response.tags;

                        $('select').material_select();

                    }, function () {
                        // do something
                    });
                }
            }

            ctrl.get = function () {
                var url = ctrl.question;

                if (ctrl.data.topic != null) {
                    url += "?topic=" + ctrl.data.topic.id;
                } else if (ctrl.data.subject != null) {
                    url += "?subject=" + ctrl.data.subject.id;
                } else if (ctrl.data.source != null) {
                    url += "?source=" + ctrl.data.source.id;
                }

                if (ctrl.tag !== "") {
                    url += "&tag=" + ctrl.tag;
                }

                window.location.href = url;
            };

            ctrl.change = function (type) {
                if (type === ctrl.type.subject) {
                    ctrl.data.topic = null;
                }

                getOptions(type)
            };

            ctrl.reset = function () {
                ctrl.data.source = null;
                ctrl.filters.subjects = [];
                ctrl.data.subject = null;
                ctrl.filters.topics = [];
                ctrl.data.topic = null;
            };

            getOptions(ctrl.type.source);
        }

        return {
            restrict: 'EA',
            templateUrl: path.asset.js + '/ngApp/directive/question-selector/q-selector.html',
            controller: qSelectorController,
            scope: {
                question: "@",
                filter: "@"
            },
            controllerAs: 'ctrl',
            bindToController: true
        };

    }

    angular
        .module("atpl")
        .directive("qSelector", qSelector);
})();