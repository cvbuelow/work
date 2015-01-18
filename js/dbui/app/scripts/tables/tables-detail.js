'use strict';
angular.module('dbui.tables.detail', ['ngRoute', 'dbui.components.api'])

  .config(function ($routeProvider) {
    $routeProvider.when('/databases/:databaseId/tables/:tableId?', {
      templateUrl: 'scripts/tables/tables-detail.html',
      controller: 'TableCtrl'
    });
  })

  .controller('TableCtrl', function($scope, $routeParams, $location, API) {
    $scope.tableId = $routeParams.tableId;

    if ($routeParams.tableId) {
      $scope.table = API.tables.get($routeParams);
    } else {
      $scope.table = new API.tables($routeParams);
    }

    API.databases.getRemoteTables($routeParams.databaseId)
      .then(function(res) {
        $scope.remoteTables = res;
      });

    $scope.save = function() {
      if ($routeParams.tableId) {
        $scope.table.$update();
      } else {
        $scope.table.$save(function(res) {
          $location.path('/tables/' + res.id + '/fields');
        });
      }
    };

  });
