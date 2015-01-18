'use strict';
angular.module('dbui.tables', [
    'ngRoute',
    'dbui.components.auth',
    'dbui.components.api',
    'dbui.tables.detail'
  ])

  .config(function ($routeProvider) {
    $routeProvider.when('/databases/:databaseId/list-tables', {
      templateUrl: 'scripts/tables/tables.html',
      controller: 'TablesCtrl'
    });
  })

  .controller('TablesCtrl', function($scope, $routeParams, API) {
    var getTables = function() {
      $scope.tables = API.tables.query($routeParams);
    };
    getTables();

    $scope.database = API.databases.get($routeParams);

    $scope.deleteTable = function(table) {
      API.tables.delete({databaseId: table.databaseId, tableId: table._id}, getTables);
    };
  });
