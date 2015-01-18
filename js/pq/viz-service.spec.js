describe('VizService', function() {
  'use strict';
  var VizService, $httpBackend, Session;

  var params = {
    usagedaterange: '2013:2014',
    pubdaterange: '1997:2014',
    formats: 'ALL',
    subjects: 'ALL'
  };

  beforeEach(function() {
    module('dissdash.viz.service');

    inject(function($injector) {
      Session = $injector.get('Session');
      Session.create({institutionId: 123, username: 'cvbuelow'});

      VizService = $injector.get('VizService');

      $httpBackend = $injector.get('$httpBackend');
      $httpBackend.when('GET', '/api/dissdash/filteroptions').respond(mock.filteroptions);
      $httpBackend.when('GET', '/api/dissdash/subjectlist').respond(mock.subjectlist);
      $httpBackend.when('GET', '/api/dissdash/userdata/cvbuelow').respond(mock.userdata);
    });
  });

  afterEach(function() {
    Session.destroy();
    $httpBackend.verifyNoOutstandingExpectation();
    $httpBackend.verifyNoOutstandingRequest();
  });

  describe('getSchools', function() {
    it('should get list of schools', function() {
      var result;
      $httpBackend.when('GET', '/api/dissdash/schoollist?institution=123').respond(mock.schoollist);
      $httpBackend.expect('GET', '/api/dissdash/schoollist?institution=123');
      VizService.getSchools().then(function(data) {
        result = data;
      });
      $httpBackend.flush();
      expect(result).toEqual(mock.schoollist.data.schools);
    });
  });

  describe('getFilters', function() {
    it('should get filter options and default values', function() {
      var result;
      VizService.getFilters().then(function(res) {
        result = res;
      });
      $httpBackend.expect('GET', '/api/dissdash/filteroptions');
      $httpBackend.expect('GET', '/api/dissdash/subjectlist');
      $httpBackend.flush();
      expect(result).toEqual(mock.filters);
    });

    it('should return a new filters object each time it is called', function() {
      var result1, result2;
      VizService.getFilters().then(function(res) {
        result1 = res;
      });
      VizService.getFilters().then(function(res) {
        result2 = res;
      });
      $httpBackend.flush();
      expect(result1).toEqual(result2);
      expect(result1).not.toBe(result2);
    });

    it('should override default filter options with saved ones', function() {
      var result1, result2;
      VizService.getFilters('FILTERS_USAGETREND').then(function(res) {
        result1 = res;
      });
      VizService.getFilters().then(function(res) {
        result2 = res;
      });
      $httpBackend.flush();
      expect(result1.values).toEqual(mock.savedUsageTrendFilters);
      expect(result2).toEqual(mock.filters);
    });

    it('should set schoolGroups to an empty object if it is undefined', function() {
      Session.create({institutionId: 123, username: 'fritz'});
      $httpBackend.when('GET', '/api/dissdash/userdata/fritz').respond(mock.userdataLess);
      var result;
      VizService.getFilters().then(function(res) {
        result = res;
      });
      $httpBackend.flush();
      expect(result.schoolGroups).toEqual({});
    });
  });

  describe('getUserData', function() {
    it('should get the user data and store it locally', function() {
      var result;
      VizService.getUserData().then(function(data) {
        result = data;
      });
      $httpBackend.expect('GET', '/api/dissdash/userdata/cvbuelow');
      $httpBackend.flush();
      expect(result).toEqual({
        FILTERS_USAGETREND: mock.savedUsageTrendFilters,
        INSTID: 1216,
        UNIVERSITY_GROUPS: {
          "1day": {
            name: 'Awesome Schools',
            schools: ['0041', '0127']
          },
          "7t9b": {
            name: 'B Schools',
            schools: ['0016', '0017', '0021', '1476']
          }
        }
      });
    });

    it('should return the same data object each time it is called', function() {
      var result1, result2;
      VizService.getUserData().then(function(data) {
        result1 = data;
      });
      VizService.getUserData().then(function(data) {
        result2 = data;
      });
      $httpBackend.flush();
      expect(result1).toBe(result2);
    });

    it('should only return the data for a given key if specified', function() {
      var result;
      VizService.getUserData('FILTERS_USAGETREND').then(function(data) {
        result = data;
      });
      $httpBackend.flush();
      expect(result).toEqual(mock.savedUsageTrendFilters);
    });
  });

  describe('setUserData', function() {
    it('should save user data to the server', function() {
      VizService.setUserData('key', 'value');
      $httpBackend.when('POST', '/api/dissdash/userdata').respond({data: {}});
      $httpBackend.expect('POST', '/api/dissdash/userdata', {
        userid: 'cvbuelow',
        key: 'key',
        type: 'OPAQUE',
        value: 'value'
      });
      $httpBackend.flush();
    });

    it('should save user data to local cache', function() {
      var result;
      VizService.getUserData().then(function() {
        VizService.setUserData('FILTERS_USAGETREND', 'bar');
        VizService.getUserData().then(function(data) {
          result = data;
        });
      });
      $httpBackend.when('POST', '/api/dissdash/userdata').respond({data: {}});
      $httpBackend.flush();
      expect(result.FILTERS_USAGETREND).toEqual('bar');
    });

    it('should reject the promise when data is not saved', function() {
      var rejected = false;
      VizService.setUserData('key', 'value').then(null, function() {
        rejected = true;
      });
      $httpBackend.when('POST', '/api/dissdash/userdata').respond({error: {}});
      $httpBackend.flush();
      expect(rejected).toBe(true);
    });
  });

  describe('buildParams', function() {
    it('should build an object to be used with an $http call', function() {
      var result = VizService.buildParams(mock.filters);
      expect(result).toEqual(params);
    });

    it('should pass subjects as a comma delimited string if subjects are selected', function() {
      var filters = mock.filters;
      filters.values.subjects = [{code: '001'}, {code: '002'}];
      var p = params;
      p.subjects = '001,002';

      var result = VizService.buildParams(filters);

      expect(result).toEqual(p);
    });

    it('should pass schoolgroup as a comma delimited string if schools are selected', function() {
      var filters = {
        values: { schoolGroup: 'abc' },
        schoolGroups: {'abc': {schools: ['001', '002']}}
      };
      var expected = '001,002';

      var result = VizService.buildParams(filters);

      expect(result.schoolgroup).toEqual(expected);
    });
  });

  describe('getChartData', function() {
    it('should get data for the specified chart', function() {
      var result;
      $httpBackend.when('GET', '/api/dissdash/usagetrend/123?formats=ALL&pubdaterange=1997:2014&subjects=ALL&usagedaterange=2013:2014').respond(mock.usagetrend);
      VizService.getFilters().then(function(filters) {
        VizService.getChartData('usagetrend', filters).then(function(data) {
          result = data;
        });
      });
      $httpBackend.flush();
      expect(result).toEqual(mock.usagetrend.data);
    });
  });

  describe('getMapData', function() {
    it('should get country names and world topology data', function() {
      var result1, result2;
      $httpBackend.when('GET', 'data/world-country-names.json').respond({"VA": "Holy See (Vatican City State)"});
      $httpBackend.expect('GET', 'data/world-country-names.json');
      $httpBackend.when('GET', 'data/worldmap.json').respond({"type":"Topology"});
      $httpBackend.expect('GET', 'data/worldmap.json');

      VizService.getMapData(function(names, map) {
        result1 = names;
        result2 = map;
      });
      $httpBackend.flush();

      expect(result1).toEqual({"VA": "Holy See (Vatican City State)"});
      expect(result2).toEqual({"type":"Topology"});
    });
  });

});
