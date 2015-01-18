'use strict';
angular.module('dbui.login', ['ngRoute', 'dbui.components.auth'])

  .config(function($routeProvider) {
    $routeProvider.when('/login', {
      templateUrl: 'scripts/login/login.html',
      controller: 'LoginCtrl',
      public: true
    });
  })

  .controller('LoginCtrl', function($scope, Auth) {
    $scope.login = function() {
      Auth.login($scope.user).then(null, function(response) {
        if (response.status === 401) {
          $scope.addAlert('Invalid username or password.');
        }
      });
    };
  })

  .directive('autoFill', function($timeout) {
    return {
      restrict: 'A',
      link: function(scope, element) {
        $timeout(function() {
          element.checkAndTriggerAutoFillEvent();
        }, 200);
      }
    };
  });
