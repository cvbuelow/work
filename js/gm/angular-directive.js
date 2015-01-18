angular.module('AppsGlobalModule').directive('scroll', function() {

  return {
    restrict: 'E',
    transclude: true,
    scope: {
      height: '=height',
      width: '=width',
      onScroll: '=',
      active: '=active',
      pager: '='
    },
    template: '<div class="scroll {{layoutClass()}}">' +
        '<div class="scroll-window" style="height:{{pageHeight}}px; width:{{pageWidth}}px">' +
          '<div style="overflow:hidden; margin-top:{{activePage * -pageHeight}}px" class="scroll-content" ng-transclude></div>' +
        '</div>' +
        '<div class="scroll-bar vertically-center" style="height:{{pageHeight - 10}}px">' +
          '<div class="scroll-arrow up" ng-class="{disabled: disableUp()}" ng-click="pageUp()"></div>' +
          '<div class="vertically-centered" ng-show="showDots()">' +
            '<div ng-repeat="i in getNumber(numPages)" class="scroll-dot {{activeClass($index)}}"></div> ' +
          '</div>' +
          '<div class="vertically-centered" ng-hide="useDots">' +
            '{{activePage + 1}}/{{numPages}}' +
          '</div>' +
          '<div class="scroll-arrow down" ng-class="{disabled: disableDown()}" ng-click="pageDown()"></div>' +
        '</div>' +
      '</div>',
    
    link: function(scope, iElement) {

      scope.pageHeight = parseInt(scope.height, 10) || 270;
      scope.pageWidth = parseInt(scope.width, 10) || 700;
      scope.activePage = parseInt(scope.active, 10) || 0;
      scope.useDots = scope.pager === 'dots';

      var content = iElement[0].querySelector('.scroll-content');

      scope.getNumber = function(num) {
        return new Array(num);
      };

      scope.pageUp = function() {
        if (scope.activePage) {
          scope.activePage--;
          scope.scroll();
        }
      };

      scope.pageDown = function() {
        if (scope.activePage < scope.numPages - 1) {
          scope.activePage++;
          scope.scroll();
        }
      };

      scope.scroll = function() {
        if (typeof scope.onScroll === 'function') {
          scope.onScroll(scope.activePage);
        }
      };

      scope.layoutClass = function() {
        return scope.numPages < 2 ? 'single' : '';
      };

      scope.activeClass = function(index) {
        return index === scope.activePage ? 'active' : '';
      };

      scope.showDots = function() {
        return scope.numPages < 7 && scope.useDots;
      };

      scope.disableUp = function() {
        return !scope.activePage;
      };

      scope.disableDown = function() {
        return scope.activePage + 1 === scope.numPages;
      };

      /* Watch for changes in content height. Height changes asynchronously. */
      scope.$watch(function() {
        return content.offsetHeight;
      }, function() {
        scope.numPages = Math.ceil(content.offsetHeight / scope.pageHeight);
        
        /* Handle edge case when number of pages decreses and active page no longer exists */
        if (scope.activePage + 1 > scope.numPages && scope.numPages) {
          scope.activePage = scope.numPages - 1;
        }

      });
    }
  };
});