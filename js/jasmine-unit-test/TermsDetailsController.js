angular.module('createUserModule')
  .controller("TermsDetailsController", ['$scope', 'AcceptTermsService', 'TermsConditionService',
    'UtilityService', 'createUserService', '$state', 'KeyboardWrapper', '$rootScope', 'DeclineTermsService', '$stateParams',
    function($scope, AcceptTermsService, TermsConditionService, UtilityService, createUserService, $state, KeyboardWrapper,
      $rootScope, DeclineTermsService, $stateParams) {
      
      UtilityService.Spinner.show();

      var docIndex = parseInt($stateParams.documentIndex, 10) || 0;
      if (docIndex > 0) {
        $scope.setHeaderClass('fullWidth pageHeader loginPage');
      }

      var getTCsDetail = createUserService.getIsNewUser() ? TermsConditionService.getAllTCs : TermsConditionService.getUpdatedTCs;
      getTCsDetail()
        .then(function() {
          $scope.page.title = TermsConditionService.getDocumentName(docIndex) + ' (' + (docIndex + 1) + '/' + TermsConditionService.getNumberOfDocuments() + ')';
          return TermsConditionService.getTCsDetailsData(docIndex);
        })
        .then(function success(data) {
            $scope.termsAndConditionsDetails = data;
            UtilityService.Spinner.hide();
          },
          function error() {
            createUserService.clearVariables();
            UtilityService.handleError('Connection Failure', 'welcome');
          });


      /**
       * Accept
       */
      $scope.setAcceptFunction(function() {
        AcceptTermsService.acceptTermsByIndex(docIndex)
          .then(function success() {
            var newDocIndex = docIndex + 1;
            if (docIndex !== '' && TermsConditionService.getNumberOfDocuments() > newDocIndex) {
              $state.go('termsAndConditions.details', {
                documentIndex: newDocIndex
              });
            } else {
              $scope.$emit('legal.terms.accepted');
            }
          }, function error() {
            UtilityService.handleError(null, backToDoc);
          });
      });


      /**
       * Email
       */
      $scope.setEmailFunction(function() {

        var opts = {
          inputType: "email",
          onSubmit: storeEmail,
          onCancel: function() {
            $scope.$apply(backToDoc);
          },
          placeholder: 'Enter Email'
        };

        KeyboardWrapper.initKeyboard(opts);
      });

      var storeEmail = function(input) {
        UtilityService.Spinner.show();
        if (UtilityService.isValidEmailId(input.value)) {
          email(input.value, docIndex);
        } else {
          $scope.showEmailError('Please enter a valid email');
        }
        $scope.$apply();
      };

      var email = function(email, index) {
        AcceptTermsService.emailTCByIndex(email, index)
          .then(function() {
            showSentMsg(email);
          }, function() {
            $scope.showEmailError("We're having trouble completing your request. Data connection is experiencing low signal strength.");
          });
      };
      
      var showSentMsg = function(email) {
        var displayEmail = email;
        if (displayEmail.length > 18) {
          email = displayEmail.substr(0, 17) + '...';
        }

        var msg = "The " + TermsConditionService.getDocumentName(docIndex) + ' has been sent to <br>' + email;
        UtilityService.showDialog('', msg, [{
          label: 'OK',
          action: backToDoc
        }], backToDoc, true);
      };
      

      /**
       * Decline
       */
      $scope.setDeclineFunction(function() {
        UtilityService.showDialog('', 'Are you sure you would like to decline the ' + TermsConditionService.getDocumentName(docIndex) + '?', [{
          label: 'Cancel',
          action: backToDoc
        }, {
          label: 'OK',
          class: 'inverted',
          action: rejectTandC
        }], backToDoc);
      });
      
      var rejectTandC = function() {
        UtilityService.Spinner.show();

        // Hide top part of page to avoid flicker once spinner is hidden when we get to the 'legal' state.
        $scope.setHeaderClass("fullWidth pageHeader loginPage noBorder");
        $scope.page.title = '';

        DeclineTermsService.rejectTandC(docIndex).then(resolveTandCPromise, resolveTandCPromise);
      };
      
      var resolveTandCPromise = function() {
        createUserService.clearVariables();
        $rootScope.$emit('legal.terms.declined');
      };

      var backToDoc = function() {
        $state.go('termsAndConditions.details', $stateParams);
      };
    }
  ]);