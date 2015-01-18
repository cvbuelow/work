'use strict';
angular.module('dissdash.schools-usage', ['ngRoute', 'dissdash.vizdirectives.hbar', 'dissdash.viz.service'])

  .config(function($routeProvider) {
    $routeProvider.when('/usage-by-other-universities', {
      templateUrl: 'scripts/schools-usage/schools-usage.html',
      controller: 'SchoolsUsageCtrl',
      role: 'user'
    });
  })

  .controller('SchoolsUsageCtrl', function($scope, VizService) {

    var filtersKey = 'FILTERS_USAGEBYOTHERUNIVERSITIES';
    $scope.topViewersOption = $scope.displaySubject = true;

    $scope.getChartData = function() {
      $scope.$broadcast('show-spinner');
      
      VizService.getChartData('usagebyschools', $scope.filters)
        .then(function(data) {
          $scope.vizData = data;
          $scope.chartData = data.usageSchools.slice(0, 10);
          $scope.$broadcast('hide-spinner');
        });
    };

    $scope.saveFilters = function() {
      VizService.setUserData(filtersKey, $scope.filters.values);
    };

    $scope.resetFilters = function() {
      return VizService.getFilters(filtersKey).then(function(filters) {
        $scope.filters = filters;
      });
    };

    $scope.resetFilters().then($scope.getChartData);

  });
