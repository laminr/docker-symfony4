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