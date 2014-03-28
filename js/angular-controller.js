angular.module('internetSourcesModule')
  .controller('connectController', ['$scope', '$rootScope', '$state', '$stateParams', 'UtilityService', 'internetSourcesService',
    function($scope, $rootScope, $state, $stateParams, UtilityService, internetSourcesService) {

      var device = $stateParams;

      var connect = function(input) {
        UtilityService.Spinner.show();

        var wifiParams = {
          SSID: device.friendlyName,
          password: input.value,
          address: device.deviceAddress,
          security: device.security
        };

        // Make framework call to connect to network
        frameWorkWrapper.setWIFIParameters(connectSuccess, connectFailure, wifiParams);
      };

      var connectSuccess = function() {
        UtilityService.Spinner.hide();
        UtilityService.showDialog('Device Tethering Complete', 'Additional data charges may apply, please consult your mobile data plan.', [{
          label: 'Done',
          class: 'confirm',
          action: doneProcess
        }], doneProcess);
        $scope.$apply();
      };

      var connectFailure = function(errorCode) {
        var msg;
        UtilityService.Spinner.hide();

        switch (errorCode) {
          case Constants.ConnectionErrorCodes.NOT_AVAILABLE:
            msg = 'The network "' + device.friendlyName + '" is no longer available.';
            break;
          case Constants.ConnectionErrorCodes.AUTH_FAILURE:
            msg = 'The password you entered is incorrect.';
            break;
          default:
            msg = 'An error occurred while trying to connect to "' + device.friendlyName + '"';
        }

        UtilityService.showDialog('Connection Failed', msg, [{
          label: 'OK',
          class: 'confirm',
          action: exitProcess
        }], exitProcess);
        $scope.$apply();
      };

      var exitProcess = function() {
        internetSourcesService.connectToEmbedded(function() {
          $state.go('deviceList');
        });
      };

      var doneProcess = function() {
        $state.go('internetSources');
      };

      $scope.modal = {
        title: 'Enter "' + UtilityService.encodeHtml(device.friendlyName) + '" Password',
        onClose: exitProcess,
        buttons: [{
          label: 'Cancel',
          class: 'alert',
          action: exitProcess
        }]
      };

      $scope.getPassword = function() {
        var opts = {
          onSubmit: connect,
          placeholder: 'Password',
          inputType: 'password'
        };
        $rootScope.initKeyboard(opts);
      };
    }
  ]);