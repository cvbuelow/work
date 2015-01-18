'use strict';
angular.module('dbui.components.api', ['ngResource'])
  .service('API', function($resource, $http) {
    var baseUrl = 'http://localhost:3000';
    var actions = {
      update: { method: 'PUT' }
    };
    this.databases = $resource(baseUrl + '/databases/:databaseId', {databaseId: '@_id'}, actions);
    this.tables = $resource(baseUrl + '/databases/:databaseId/tables/:tableId', {databaseId: '@databaseId', tableId: '@_id'}, actions);
    this.fields = $resource(baseUrl + '/tables/:tableId/fields/:fieldId', null, actions);
    this.records = $resource(baseUrl + '/tables/:tableId/records/:recordId', null, actions);

    this.databases.test = function(database) {
      return $http.post(baseUrl + '/databases/test', database);
    };

    this.databases.getRemoteTables = function(databaseId) {
      return $http.get(baseUrl + '/databases/' + databaseId + '/remote-tables')
        .then(function(res) {
          return res.data[0].map(function(obj) {
            return obj[Object.keys(obj)[0]];
          });
        });
    };
  });
