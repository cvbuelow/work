angular.module('createUserModule')
  .controller("TermsDetailsController", ['$scope', 'AcceptTermsService', 'TermsConditionService',
        'UtilityService', 'createUserService', '$state', 'KeyboardWrapper', '$rootScope', 'DeclineTermsService', '$stateParams',
  function($scope, AcceptTermsService, TermsConditionService, UtilityService, createUserService, $state, KeyboardWrapper,
            $rootScope, DeclineTermsService, $stateParams){
    UtilityService.Spinner.show();

    var docIndex = parseInt($stateParams.documentIndex, 10) || 0;
    if (docIndex > 0) {
      $scope.setHeaderClass('fullWidth pageHeader loginPage');
    }

    var reRoute = function() {
      UtilityService.Spinner.show();
      var newDocIndex = docIndex + 1;
      if ($stateParams.documentIndex !== '' && TermsConditionService.getNumberOfDocuments() > newDocIndex){
        $state.go('termsAndConditions.details', {documentIndex: newDocIndex});
      } else {
        $rootScope.$emit('legal.terms.accepted');
      }
    };

    var showSentMsg = function(email) {
      var displayEmail = email;
      if (displayEmail.length > 18) {
        email = displayEmail.substr(0,17)+'...' ;
      }
          
      var msg = "The " + TermsConditionService.getDocumentName(docIndex) + ' has been sent to ';
      UtilityService.showDialog(msg, email, [{
        label: 'OK',
        action: function(){
          $state.go('termsAndConditions.details', {documentIndex: docIndex});
        }
      }], function(){
        $state.go('termsAndConditions.details', {documentIndex: docIndex});
      }, true);
    };
    var acceptTCsFailure = function(failure) {
      UtilityService.showDialog('', 'Unable to connect', [{
        class: 'inverted',
        label: 'OK',
        action: function() {
          $state.go('termsAndConditions.details', {documentIndex : docIndex});
        }
      }], function() {
        $state.go('termsAndConditions.details', {documentIndex : docIndex});
      });
    };
    var email = function(email, index) {
      UtilityService.Spinner.show();
      AcceptTermsService.emailTCByIndex(email, index)
      .then(function(){
        showSentMsg(email);
      }, function() {
        $scope.showEmailError('Unable to send email');
      });
    };

    var storeEmail = function(input) {
      UtilityService.Spinner.show();
      if (UtilityService.isValidEmailId(input.value)) {
        email(input.value, docIndex);
      } else {
        $scope.showEmailError('Please enter a valid email');
      }
      $scope.$apply();
    };

    var getTCsDetail = createUserService.getIsNewUser() ? TermsConditionService.getAllTCs : TermsConditionService.getUpdatedTCs;
    getTCsDetail()
      .then(function(){
        $scope.page.title = TermsConditionService.getDocumentName(docIndex) + ' (' + (docIndex + 1) + '/' + TermsConditionService.getNumberOfDocuments() + ')';
        return TermsConditionService.getTCsDetailsData(docIndex);
      })
      .then(function success(data){
        $scope.termsAndConditionsDetails = data;
        UtilityService.Spinner.hide();
      },
      function error(){
        createUserService.clearVariables();
        UtilityService.handleError('Connection Failure', 'welcome');
      });

    $scope.setAcceptFunction( function(){
      AcceptTermsService.acceptTermsByIndex(docIndex).
        then(function(){
          var newDocIndex = docIndex + 1;
          if (docIndex !== '' && TermsConditionService.getNumberOfDocuments() > newDocIndex){
            $state.go('termsAndConditions.details', {documentIndex: newDocIndex});
          } else {
            $scope.$emit('legal.terms.accepted');
          }
        }, acceptTCsFailure);
    });

    $scope.cancelPressedInEmailEntry = function() {
        // without this, the user cannot cancel after they have entered an invalid email.

        if(!$scope.previousState.docIndex)
        {
          $state.go($scope.previousState.name); // always allow the user to close the email entry if the user clicks cancel
        }
        if(docIndex)
        {
          $state.go($scope.previousState.name, {documentIndex: $scope.previousState.docIndex});
        }
        //why not just modal.close()...
      };

    $scope.setEmailFunction( function() {

      var opts = {
        inputType: "email",
        onSubmit: storeEmail,
        onCancel: $scope.cancelPressedInEmailEntry,
        placeholder: 'Enter Email'
      };

      // without this, the user cannot cancel after they have entered an invalid email.
      if($state.current.name !== "modal")
      {
        $scope.previousState = {
          name: $state.current.name,
          docIndex: parseInt($stateParams.documentIndex, 10) || 0
        };
      }
      if($state.current.name === "modal")
      {
        $state.go($scope.previousState.name, {documentIndex: $scope.previousState.docIndex});
      }

      KeyboardWrapper.initKeyboard(opts);
    });
    var resolveTandCPromise = function(){
      createUserService.clearVariables();
      $rootScope.$emit('legal.terms.declined');
    };
    var rejectTandC = function() {
      UtilityService.Spinner.show();
      
      // Hide top part of page to avoid flicker once spinner is hidden when we get to the 'legal' state.
      $scope.setHeaderClass("fullWidth pageHeader loginPage noBorder");
      $scope.page.title = '';
      
      DeclineTermsService.rejectTandC(docIndex).then(resolveTandCPromise, resolveTandCPromise);
    };
    $scope.setDeclineFunction( function(){
      UtilityService.showDialog('', 'Are you sure you would like to decline the ' + TermsConditionService.getDocumentName(docIndex) + '?', [{
        label: 'Cancel',
        action: function(){
          $state.go('termsAndConditions.details', {documentIndex : docIndex});
        }
      }, {
        label: 'OK',
        class: 'inverted',
        action: rejectTandC
      }], function(){
        $state.go('termsAndConditions.details', {documentIndex : docIndex});
      });
    });

  }]);
