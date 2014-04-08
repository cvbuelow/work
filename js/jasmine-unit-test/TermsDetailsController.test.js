describe('TermsDetailsController', function() {
  var run, $scope;

  beforeEach(function() {
    angular.mock.module('createUserModule');
    module(function($provide) {
      
      var UtilityService = jasmine.createSpyObj('UtilityService', ['handleError', 'isValidEmailId', 'showDialog']);
      UtilityService.Spinner = jasmine.createSpyObj('UtilityService.Spinner', ['hide', 'show']);
      
      $provide.value('TermsConditionService', jasmine.createSpyObj('TermsConditionService', [
        'getDocumentName',
        'getNumberOfDocuments',
        'getTCsDetailsData',
        'getAllTCs',
        'getUpdatedTCs'
      ]));
      $provide.value('AcceptTermsService', jasmine.createSpyObj('AcceptTermsService', ['acceptTermsByIndex', 'emailTCByIndex']));
      $provide.value('UtilityService', UtilityService);
      $provide.value('createUserService', jasmine.createSpyObj('createUserService', ['getIsNewUser', 'clearVariables']));
      $provide.value('KeyboardWrapper', jasmine.createSpyObj('KeyboardWrapper', ['initKeyboard']));
      $provide.value('DeclineTermsService', jasmine.createSpyObj('DeclineTermsService', ['rejectTandC']));
      $provide.value('$state', jasmine.createSpyObj('$state', ['go']));
    });

    inject(function($injector, TermsConditionService) {
      var $controller = $injector.get('$controller');
      var $q = $injector.get('$q');
      var $rootScope = $injector.get('$rootScope');
      $scope = jasmine.createSpyObj('$scope', [
        'setHeaderClass', 
        'setAcceptFunction',
        'setEmailFunction',
        'setDeclineFunction',
        'showEmailError',
        '$emit',
        '$apply'
      ]);
      $scope.page = {};
      
      TermsConditionService.getAllTCs.andReturn($q.when());
      TermsConditionService.getUpdatedTCs.andReturn($q.when());

      run = function(documentIndex) {
        $controller('TermsDetailsController', {
          $rootScope: $rootScope,
          $scope: $scope, 
          $stateParams: {
            documentIndex: documentIndex
          }
        });
      };

    });
  });

  angular.forEach([0, 1], function(docIndex) {

    beforeEach(inject(function($state) {
      this.testBackToDoc = function(index) {
        expect($state.go).toHaveBeenCalledWith('termsAndConditions.details', {documentIndex: index});
      };
    }));

    describe('what happens when page ' + (docIndex + 1) + ' loads', function() {

      it('should show the spinner and bind actions to buttons', inject(function(UtilityService, createUserService) {
        
        run(docIndex);
        
        if (docIndex > 0) {
          expect($scope.setHeaderClass).toHaveBeenCalledWith('fullWidth pageHeader loginPage');
        }
        expect(UtilityService.Spinner.show).toHaveBeenCalled();
        expect(createUserService.getIsNewUser).toHaveBeenCalled();
        expect($scope.setAcceptFunction).toHaveBeenCalledWith(jasmine.any(Function));
        expect($scope.setEmailFunction).toHaveBeenCalledWith(jasmine.any(Function));
        expect($scope.setDeclineFunction).toHaveBeenCalledWith(jasmine.any(Function));

      }));

      describe('retrieving page content', function() {
        
        it('should get a list of all T&Cs if this is a new user', inject(function(createUserService, TermsConditionService) {
          createUserService.getIsNewUser.andReturn(true);
          run(docIndex);
          expect(TermsConditionService.getAllTCs).toHaveBeenCalled();
        }));

        it('should get a list of updated T&Cs if this is not a new user', inject(function(createUserService, TermsConditionService) {
          createUserService.getIsNewUser.andReturn(false);
          run(docIndex);
          expect(TermsConditionService.getUpdatedTCs).toHaveBeenCalled();
        }));

        describe('when the list of T&Cs is retrieved', function() {
          it('should get the T&C document specified by the index', inject(function($q, $rootScope, UtilityService, TermsConditionService) {
            TermsConditionService.getDocumentName.andReturn('T&Cs');
            TermsConditionService.getNumberOfDocuments.andReturn(2);
            TermsConditionService.getTCsDetailsData.andReturn($q.when('<div>TC</div>'));
            
            run(docIndex);
            $rootScope.$apply();
            
            expect($scope.page.title).toBe('T&Cs (' + (docIndex + 1) + '/2)');
            expect(TermsConditionService.getDocumentName).toHaveBeenCalledWith(docIndex);
            expect(TermsConditionService.getNumberOfDocuments).toHaveBeenCalled();
            expect(TermsConditionService.getTCsDetailsData).toHaveBeenCalledWith(docIndex);
            expect(UtilityService.Spinner.hide).toHaveBeenCalled();
            expect($scope.termsAndConditionsDetails).toBe('<div>TC</div>');
          }));

          it('should show an error message if retrieving T&Cs failed', inject(function($q, $rootScope, createUserService, UtilityService, TermsConditionService) {
            TermsConditionService.getUpdatedTCs.andReturn($q.reject());
            
            run(docIndex);
            $rootScope.$apply();
            
            expect(createUserService.clearVariables).toHaveBeenCalled();
            expect(UtilityService.handleError).toHaveBeenCalledWith('Connection Failure', 'welcome');
          }));
        });
      });
    });

    describe('what the buttons on page ' + (docIndex + 1) + ' do', function() {

      beforeEach(function() {
        run(docIndex);
        this.acceptButton = $scope.setAcceptFunction.mostRecentCall.args[0];
        this.emailButton = $scope.setEmailFunction.mostRecentCall.args[0];
        this.declineButton = $scope.setDeclineFunction.mostRecentCall.args[0];
      });
      
      describe('clicking accept', function() {
        
        it('should accept the current document and go to the next one', inject(function($q, $rootScope, $state, AcceptTermsService, TermsConditionService) {
          AcceptTermsService.acceptTermsByIndex.andReturn($q.when());
          TermsConditionService.getNumberOfDocuments.andReturn(3);
          
          this.acceptButton();
          $rootScope.$apply();
          
          expect(AcceptTermsService.acceptTermsByIndex).toHaveBeenCalledWith(docIndex);
          expect(TermsConditionService.getNumberOfDocuments).toHaveBeenCalled();
          expect($state.go).toHaveBeenCalledWith('termsAndConditions.details', {documentIndex: docIndex + 1});
        }));
        
        it('should let the app know if all T&Cs are accepted', inject(function($q, $rootScope, AcceptTermsService, TermsConditionService) {
          AcceptTermsService.acceptTermsByIndex.andReturn($q.when());
          TermsConditionService.getNumberOfDocuments.andReturn(1);
          
          this.acceptButton();
          $rootScope.$apply();
          
          expect($scope.$emit).toHaveBeenCalledWith('legal.terms.accepted');
        }));

        it('should show an error message if accepting failed', inject(function($q, $rootScope, AcceptTermsService, UtilityService) {
          AcceptTermsService.acceptTermsByIndex.andReturn($q.reject());
          this.acceptButton();
          $rootScope.$apply();
          expect(UtilityService.handleError).toHaveBeenCalledWith(null, jasmine.any(Function));
        }));

      });

      describe('clicking email', function() {
        
        beforeEach(inject(function(KeyboardWrapper) {
          this.emailButton();
          this.keyboardSubmit = KeyboardWrapper.initKeyboard.mostRecentCall.args[0].onSubmit;
          this.keyboardCancel = KeyboardWrapper.initKeyboard.mostRecentCall.args[0].onCancel;
        }));

        it('should show the keyboard', inject(function(KeyboardWrapper) {
          expect(KeyboardWrapper.initKeyboard).toHaveBeenCalledWith(jasmine.any(Object));
        }));

        it('should show an error message if an invalid email is submitted', inject(function(UtilityService) {
          UtilityService.isValidEmailId.andReturn(false);
          
          this.keyboardSubmit({value: 'foo'});
          
          expect(UtilityService.Spinner.show).toHaveBeenCalled();
          expect(UtilityService.isValidEmailId).toHaveBeenCalledWith('foo');
          expect($scope.showEmailError).toHaveBeenCalledWith('Please enter a valid email');
        }));

        it('should go back to the document if cancel is pressed on the keyboard', inject(function($state) {
          this.keyboardCancel();
          var backToDoc = $scope.$apply.mostRecentCall.args[0];
          backToDoc();
          
          expect($scope.$apply).toHaveBeenCalledWith(jasmine.any(Function));
          this.testBackToDoc(docIndex);
        }));

        it('should send an email if a valid email is submitted and show a confirmation message', inject(function($q, $rootScope, UtilityService, AcceptTermsService, TermsConditionService) {
          var email = 'foo@bar.com';
          UtilityService.isValidEmailId.andReturn(true);
          AcceptTermsService.emailTCByIndex.andReturn($q.when());
          
          this.keyboardSubmit({value: email});
          $rootScope.$apply();
          
          expect(UtilityService.Spinner.show).toHaveBeenCalled();
          expect(UtilityService.isValidEmailId).toHaveBeenCalledWith(email);
          expect(AcceptTermsService.emailTCByIndex).toHaveBeenCalledWith(email, docIndex);
          expect(TermsConditionService.getDocumentName).toHaveBeenCalledWith(docIndex);
          expect(UtilityService.showDialog).toHaveBeenCalledWith(jasmine.any(String), jasmine.any(String), jasmine.any(Array), jasmine.any(Function), true);
        }));

        it('should truncate the email in the confirmation message if it is too long', inject(function($q, $rootScope, UtilityService, AcceptTermsService, TermsConditionService) {
          var email = 'foofoofoo@barbarbar.com';
          var docTitle = 'Awesome Terms and Conditions';
          UtilityService.isValidEmailId.andReturn(true);
          AcceptTermsService.emailTCByIndex.andReturn($q.when());
          TermsConditionService.getDocumentName.andReturn(docTitle);
          
          this.keyboardSubmit({value: email});
          $rootScope.$apply();
                  
          var msg = UtilityService.showDialog.mostRecentCall.args[1];
          var backToDoc = UtilityService.showDialog.mostRecentCall.args[3];
          expect(msg).toContain(docTitle);
          expect(msg).toContain('foofoofoo@barbarb...');

          backToDoc();
          this.testBackToDoc(docIndex);
        }));

        it('should show an error message if sending email failed', inject(function($q, $rootScope, UtilityService, AcceptTermsService) {
          var email = 'foo@bar.com';
          UtilityService.isValidEmailId.andReturn(true);
          AcceptTermsService.emailTCByIndex.andReturn($q.reject());
          
          this.keyboardSubmit({value: email});
          $rootScope.$apply();
          
          expect($scope.showEmailError).toHaveBeenCalledWith("We're having trouble completing your request. Data connection is experiencing low signal strength.");
        }));

      });

      describe('clicking decline', function() {
        it('should prompt the user to confirm before declining and go back to document on cancel', inject(function(UtilityService, TermsConditionService) {
          var docTitle = 'Awesome Terms and Conditions';
          TermsConditionService.getDocumentName.andReturn(docTitle);
          
          this.declineButton();
          var msg = UtilityService.showDialog.mostRecentCall.args[1];
          var backToDoc = UtilityService.showDialog.mostRecentCall.args[3];
          
          backToDoc();
          this.testBackToDoc(docIndex);
          expect(msg).toContain(docTitle);
          expect(TermsConditionService.getDocumentName).toHaveBeenCalledWith(docIndex);
          expect(UtilityService.showDialog).toHaveBeenCalledWith(jasmine.any(String), jasmine.any(String), jasmine.any(Array), jasmine.any(Function));
        }));

        it('should reject the T&Cs if user clicks ok and return to app flow', inject(function($q, $rootScope, UtilityService, DeclineTermsService, createUserService) {
          DeclineTermsService.rejectTandC.andReturn($q.when());
          spyOn($rootScope, '$emit');
          
          this.declineButton();
          var rejectTandC = UtilityService.showDialog.mostRecentCall.args[2][1].action;
          rejectTandC();
          $rootScope.$apply();
          
          expect(UtilityService.Spinner.show).toHaveBeenCalled();
          expect($scope.setHeaderClass).toHaveBeenCalledWith('fullWidth pageHeader loginPage noBorder');
          expect(DeclineTermsService.rejectTandC).toHaveBeenCalledWith(docIndex);
          expect(createUserService.clearVariables).toHaveBeenCalled();
          expect($rootScope.$emit).toHaveBeenCalledWith('legal.terms.declined');
        }));
      });

    });
  
  });

});