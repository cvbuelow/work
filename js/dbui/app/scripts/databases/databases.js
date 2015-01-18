'use strict';
angular.module('dbui.databases', [
    'ngRoute',
    'dbui.components.auth',
    'dbui.components.api',
    'dbui.databases.detail'
  ])

  .config(function ($routeProvider) {
    $routeProvider.when('/', {
      templateUrl: 'scripts/databases/databases.html',
      controller: 'DatabasesCtrl'
    });
  })

  .controller('DatabasesCtrl', function($scope, API) {
    var getDatabases = function() {
      $scope.databases = API.databases.query();
    };
    getDatabases();

    $scope.deleteDatabase = function(db) {
      API.databases.delete({databaseId: db._id}, getDatabases);
    };
  });
