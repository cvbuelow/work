'use strict';
angular.module('dbui.components.session', ['ngCookies'])
  .service('Session', function ($cookieStore) {

    this.create = function (user) {
      this.user = user;
      $cookieStore.put('user', user);
    };

    this.destroy = function () {
      delete this.user;
      $cookieStore.remove('user');
    };

    this.restore = function() {
      var user = $cookieStore.get('user');
      if (user) {
        this.create(user);
      }
      return user;
    };

    this.isAuthenticated = function() {
      return !!this.user;
    };
  });
