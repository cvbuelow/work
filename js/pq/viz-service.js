'use strict';
angular.module('dissdash.viz.service', ['dissdash.session'])
  .service('VizService', function($http, $q, Session) {

    var institutionId = function() {
      return Session.user && Session.user.institutionId;
    };

    var filteroptions = function() {
      return $http.get('/api/dissdash/filteroptions', {cache: true})
        .then(function(response) {
          var data = response.data.data;
          data.values = {
            usageYearFrom: data.usageYearDefaults[0],
            usageYearTo: data.usageYearDefaults[1],
            pubYearFrom: data.publishedYearDefaults[0],
            pubYearTo: data.publishedYearDefaults[1],
            format: data.formatsDefault,
            subjects: []
          };
          return data;
        });
    };

    var subjectlist = function() {
      return $http.get('/api/dissdash/subjectlist', {cache: true})
        .then(function(response) {
          return response.data.data.subjects;
        });
    };

    var getSchools = function() {
      return $http.get('/api/dissdash/schoollist', {
          cache: true,
          params: {institution: institutionId()}
        })
        .then(function(response) {
          return response.data.data.schools;
        });
    };

    var getFilters = function(chart) {
      return $q.all([filteroptions(), subjectlist(), getUserData()])
        .then(function(responses) {
          responses[0].subjectOptions = responses[1];
          responses[0].schoolGroups = responses[2].UNIVERSITY_GROUPS || {};
          var filters = angular.copy(responses[0]);
          angular.extend(filters.values, responses[2][chart]);
          return filters;
        });
    };

    var userData = {};
    var getUserData = function(key) {
      return $http.get('/api/dissdash/userdata/' + (Session.user && Session.user.username), {cache: true})
        .then(function(response) {
          if (!response.config.cached) {
            angular.forEach(response.data.data.dataItems, function(item) {
              try {
                userData[item.key] = JSON.parse(item.value);
              } catch(e) {}
            });
          }
          if (key) {
            return userData[key];
          }
          return userData;
        });
    };

    var setUserData = function(key, value) {
      return $http.post('/api/dissdash/userdata', {
          userid: Session.user.username,
          key: key,
          type: 'OPAQUE',
          value: value
        })
        .then(function(response) {
          if (response.data.error) {
            return $q.reject();
          }
          userData[key] = value;
        });
    };

    var buildParams = function(filters) {
      var subjects = [];
      angular.forEach(filters.values.subjects, function(subject) {
        subjects.push(subject.code);
      });

      var schoolGroup = filters.schoolGroups[filters.values.schoolGroup];

      return {
        usagedaterange: filters.values.usageYearFrom + ":" + filters.values.usageYearTo,
        pubdaterange: filters.values.pubYearFrom + ":" + filters.values.pubYearTo,
        formats: filters.values.format,
        subjects: subjects.length ? subjects.toString() : 'ALL',
        schoolgroup: schoolGroup && schoolGroup.schools.toString()
      };
    };

    var getChartData = function(chart, filters) {
      var config = {
        url: '/api/dissdash/' + chart + '/' + institutionId(),
        params: buildParams(filters),
        cache: true
      };

      return $http(config)
        .then(function(response) {
          return response.data.data;
        });
    };

    var getMapData = function(fn) {
      $q.all([
        $http.get('data/world-country-names.json', {cache: true}),
        $http.get('data/worldmap.json', {cache: true})
      ])
        .then(function(responses) {
          fn(responses[0].data, responses[1].data);
        });
    };

    return {
      getSchools: getSchools,
      getFilters: getFilters,
      getUserData: getUserData,
      setUserData: setUserData,
      buildParams: buildParams,
      getChartData: getChartData,
      getMapData: getMapData
    };
  });
