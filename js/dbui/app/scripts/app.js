'use strict';

angular.module('dbui', [
    'ngRoute',
    'ui.bootstrap',
    'dbui.components.auth',
    'dbui.login',
    'dbui.databases',
    'dbui.tables',
    'dbui.fields'
  ])

  .config(function($routeProvider) {
    $routeProvider
      .otherwise({
        redirectTo: '/'
      });
  })

  .controller('AppCtrl', function($scope, $rootScope, $location, $exceptionHandler, Auth, Session) {

    // Try to restore session
    Session.restore();

    var gotoLogin = function() {
      $location.path('/login');
    };
    var gotoDash = function() {
      $location.path('/');
    };

    $rootScope.$on('auth-login-success', gotoDash);
    $rootScope.$on('auth-not-authorized', gotoDash);

    $rootScope.$on('auth-logout-success', gotoLogin);
    $rootScope.$on('auth-not-authenticated', Auth.logout);

    $rootScope.$on('http-error', function() {
      $scope.addAlert('Error');
    });

    $rootScope.$on('$routeChangeStart', function (event, next) {
      if (!next.public && !Session.isAuthenticated()) {
        $rootScope.$broadcast('auth-not-authenticated');
      }
    });

    $rootScope.$on('$routeChangeSuccess', function() {
      $scope.alerts = [];
    });

    $scope.logout = Auth.logout;
    $scope.isAuthenticated = function() {
      return Session.isAuthenticated();
    };

    $scope.alerts = [];
    $scope.closeAlert = function(index) {
      $scope.alerts.splice(index, 1);
    };
    $scope.addAlert = function(msg, type) {
      $scope.alerts.push({
        msg: msg,
        type: type || 'danger'
      });
    };
  });
