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
(function () {
    'use strict';

    // trustHtml.$inject = ['$sce'];
    //
    // function trustHtml($sce) {
    //     return function (htmlCode) {
    //         return $sce.trustAsHtml(htmlCode);
    //     }
    // }
    //

    // function split() {
    //     return function(input, splitChar) {
    //         //if (input === undefined || input.length == 0) return [];
    //
    //         return input.split(splitChar);
    //     }
    // }
    //
    // angular
    //     .module('atplFilter', [])
    //     .filter('split', split);

})();
(function() {
	'use strict';

    httpService.$inject = ['$http'];

	function httpService($http) {

        function _get(url, callback) {

            // $http.defaults.headers.common['Authorization'] = $cookies.get('PHPSESSIONID');

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
(function(){
	'use strict';

	question.$inject = ['$sce'];

	function question($sce) {

 		function questionController() {

			var ctrl = this;

            var showResult = function() {
                ctrl.show = !ctrl.show;
            };

            ctrl.urlEdit = function (id) {
                return path.edit.replace('questionId', id);
            };

            ctrl.select = function(answer) {
            	ctrl.answer = answer;
            	showResult();
			};

            ctrl.renderHtml = function(html_code) {
                return $sce.trustAsHtml(html_code);
            };

            ctrl.splitImg = function() {
            	if (ctrl.question.img !== undefined && ctrl.question.img !== null) {
                    var imgs = ctrl.question.img.split('|');
                    for (var i=0; i<imgs.length; i++) {
                    	imgs[i] = path.img + "/"+ imgs[i];
					}

					return imgs;
				}
            	return [];
			};

            // init the modal for explain
            $('.modal').modal();
		}

		return  {
			restrict: 'EA',
			templateUrl: path.asset.js + '/ngApp/directive/question/question.html',
			controller: questionController,
			scope: {
				question : "=",
				show: "=",
				answer: "=",
				isAdmin: "="
			},
			// link: link,
			controllerAs: 'ctrl',
			bindToController: true
		};

	}

	angular
		.module("atpl")
		.directive("ebQuestion", question);
})();
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
(function(){
	'use strict';

    topic.$inject = ['$sce', 'httpService'];

	function topic($sce, httpService) {

 		function topicController() {

			var ctrl = this;
			ctrl.topic = data;
			ctrl.url = {
			    question: path.question,
                focus : path.focus,
                follow: path.follow
            };

			ctrl.loading = {
			    left: false,
                right: false
            };

			ctrl.index = 0;
			ctrl.active = ctrl.topic.questions[ctrl.index];
			ctrl.answer = null;
			ctrl.done = false;

			ctrl.img = [];
			ctrl.show = false;
			ctrl.follow = false;

            function shuffle(a) {
                var j, x, i, times;
                for (times = 0; times < 10; times++){
                    for (i = a.length; i; i--) {
                        j = Math.floor(Math.random() * i);
                        x = a[i - 1];
                        a[i - 1] = a[j];
                        a[j] = x;
                    }
                }
            }

            function asyncQuestion() {
            	var id = ctrl.topic.questions[ctrl.index];
                var url =  ctrl.url.question.replace('questionId', id);
                httpService.get(url, function (response) {
                    if (response.success) {
                        shuffle(response.data.answers);
                        ctrl.active = response.data;
                        ctrl.show = false;
                        ctrl.answer = null;
                    }
                    resetLoading();
                }, function () {
                    resetLoading();
                });
            }

            function followQuestion() {

            	var id = ctrl.topic.questions[ctrl.index];
            	var good = ctrl.answer.good ? 1 : 0;
                var url =  ctrl.url.follow.replace('questionId', id).replace('good', good);

                httpService.get(url, function () {
                    resetLoading();
                });
            }

            function setNextIndex(side) {
                var nextQ = side == 1 ? ctrl.index + 1 : ctrl.index -1;
                if (nextQ >= 0 && nextQ < ctrl.topic.questions.length) {
                    ctrl.index = nextQ;
                }
            }

            function loadQuestion() {
                if (ctrl.async) {
                    asyncQuestion();
                } else {
                    shuffle(ctrl.topic.questions[ctrl.index].answers);
                    ctrl.active = ctrl.topic.questions[ctrl.index];
                    ctrl.show = false;
                    ctrl.answer = null;
                    resetLoading();
                }
            }

            function resetLoading() {
                ctrl.loading.left = false;
                ctrl.loading.right = false;
            }

            ctrl.urlEdit = function (id) {
                return path.edit.replace('questionId', id);
            };

            ctrl.shuffleQuestions = function () {
                shuffle(ctrl.topic.questions);
                loadQuestion();
            };

            ctrl.subjectUrl = function () {
            	return	path.source.replace('sourceId', ctrl.topic.subject.sourceId) + "#subject_"+ctrl.topic.subject.id;
			};

            ctrl.nextTopic = function(side) {
				var url = "";

				if (side == -1) {
					url = path.topic.replace("topicId", ctrl.topic.previous);
				} else {
                    url = path.topic.replace("topicId", ctrl.topic.next);
				}
				window.location.href = url;
			};

            ctrl.next = function(side) {

			    ctrl.done = side === 0;

			    var isRight = (side === 0 || side === 1);
                ctrl.loading.right = isRight;
                ctrl.loading.left = !isRight;

			    if (ctrl.follow && ctrl.answer != null) {
                    followQuestion();
                }

                if (side !== 0) {
                    setNextIndex(side);
                    loadQuestion();
                }
			};

			ctrl.saveFocus = function(type) {
			    var url = type === 0 ? ctrl.url.focus.dont_care : ctrl.url.focus.important;
                httpService.get(url.replace("questionId", ctrl.active.id), function (response) {
                    if (response.success) {
                        //loadQuestion()
                        ctrl.active.focus = response.data.focus;

                        if (!ctrl.async) {
                            ctrl.topic.questions[ctrl.index].focus = response.data.focus;
                        }
                    }
                });
            };

            ctrl.renderHtml = function(html_code) {
                return $sce.trustAsHtml(html_code);
            };

            // loading first question
			if (ctrl.async) loadQuestion();
		}

		return  {
			restrict: 'EA',
			templateUrl: path.asset.js + '/ngApp/directive/topic/topic.html',
			controller: topicController,
			scope: {
				async: "=",
                isAdmin: "="
			},
			controllerAs: 'ctrl',
			bindToController: true
		};

	}

	angular
		.module("atpl")
		.directive("ebTopic", topic);
})();