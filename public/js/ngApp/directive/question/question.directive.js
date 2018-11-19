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