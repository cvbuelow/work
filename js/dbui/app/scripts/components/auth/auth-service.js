'use strict';
angular.module('dbui.components.auth', ['dbui.components.http', 'dbui.components.session'])
  .service('Auth', function($http, $rootScope, Session) {

    this.login = function(user) {
      return $http.post('http://localhost:3000/login', user)
        .then(function(response) {
          Session.create(response.data);
          $rootScope.$broadcast('auth-login-success');
        });
    };

    this.logout = function() {
      Session.destroy();
      $rootScope.$broadcast('auth-logout-success');
    };

  });
