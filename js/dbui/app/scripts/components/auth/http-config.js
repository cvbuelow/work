'use strict';
angular.module('dbui.components.http', ['dbui.components.session'])

  .config(function($httpProvider) {
    $httpProvider.interceptors.push('authInterceptor');
    if (!$httpProvider.defaults.headers.get) {
      $httpProvider.defaults.headers.get = {};
    }
    //disable IE ajax request caching
    $httpProvider.defaults.headers.get['If-Modified-Since'] = '0';
  })

  .factory('authInterceptor', function($rootScope, $q, Session) {
    return {
      request: function(config) {
        config.headers = config.headers || {};
        if (Session.isAuthenticated()) {
          config.headers.Authorization = 'Bearer ' + Session.user.token;
        }
        return config;
      },
      responseError: function(response) {
        if (response.status === 401) {
          $rootScope.$broadcast('auth-not-authenticated');
        } else if (response.status === 403) {
          $rootScope.$broadcast('auth-not-authorized');
        } else {
          $rootScope.$broadcast('http-error');
        }
        return $q.reject(response);
      }
    };
  });

