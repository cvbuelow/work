'use strict';
angular.module('dbui.fields', [
    'ngRoute',
    'dbui.components.api',
    // 'dbui.fields.detail'
  ])

  .config(function ($routeProvider) {
    $routeProvider.when('/tables/:tableId/fields', {
      templateUrl: 'scripts/fields/fields.html',
      controller: 'FieldsCtrl'
    });
  })

  .controller('FieldsCtrl', function($scope, $routeParams, API) {
    var getFields = function() {
      $scope.fields = API.fields.query($routeParams);
    };
    getFields();

    // $scope.database = API.databases.get($routeParams);

    $scope.deleteField = function(field) {
      API.fields.delete({tableId: field.tableId, fieldId: field._id}, getFields);
    };
  });
