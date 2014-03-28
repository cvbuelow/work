angular.module('createUserModule')
  .service('RoutingService', ['$q', '$rootScope', '$state', 'UserAppsService', 'SessionManagerService', 'TermsConditionService', 'createUserService',
    function($q, $rootScope, $state, UserAppsService, SessionManagerService, TermsConditionService, createUserService) {

      var checkUserExists = function(token) {
        var username        = String(token.username),
            isEmulator      = username === 'DefaultUser' && (token.emulator || token.deviceAddress === "00:11:22:AA:BB:CC"),
            isNewUser       = isEmulator || !username || username === "0",
            migrationNeeded = !isNewUser && username !== "1";
        
        if (migrationNeeded) {
          SessionManagerService.setNeedMigration({needed: true, auth: token.authToken});
        }
        if (isNewUser || migrationNeeded) {
          createUserService.setIsNewUser(true);
          return $q.reject();
        }
      };

      var checkForUpdatedTC = function() {
        return TermsConditionService.getUpdatedTCs()
          .then(
            function(data) {
              // Determine whether user has accepted all T&Cs
              if (data.TCInfo && data.TCInfo.length) {
                return $q.reject();
              }
          });
      };

      var allAccepted = function() {
        $rootScope.$emit('legal.terms.accepted');
      };

      var startTCFlow = function() {
        $state.go('welcome');
      };

      return {
        getAppStartUserRoute: function() {
          UserAppsService.getCurrentUserAuthToken()
            .then( checkUserExists )
            .then( checkForUpdatedTC )
            .then( allAccepted, startTCFlow );
        }
      };
    }
  ]);