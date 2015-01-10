var spinApp = angular.module('spinApp', []);
spinApp.controller('spinCtrl', function($scope, $http, $timeout, $document){
    // Function create request
    function _request(requestName, args = {}, func, pathToProcess = tempDirUri + '/js/spin/process.php'){
        var requestName = $http({
            method: "post",
            url: pathToProcess,
            data: args,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        });
        requestName.success(func);
    }
    // Init
    $scope.messCalculate = 'Available Request';
    //$scope.mess = 'Post Spinner';
    $scope.oldDomain = 'Savings.com';
    $scope.newDomain = 'StoreCoupons.me';
    $scope.protectKeyword = 'coupon,coupons,promo,promos,promotion,code,codes,discount,voucher,vouchers,free shipping';
    // Spin now
    $scope.spinNow = function(){
        spin();
    }
    function spin(){
        $scope.isShow = true;
        var $postID = angular.element(document.querySelector('#postID'));
         _request('requestBeforeSpin', {
            myID : $postID.text(),
            action : 'spin',
            configEmail : $scope.configEmail,
            apiKey : $scope.apiKey,
            oldDomain : $scope.oldDomain,
            newDomain : $scope.newDomain,
            protectKeyword : $scope.protectKeyword
        },function (response) {
            $scope.isShow = false;
            $postID.remove();
            console.log(response);
            $scope.spined += 1;
            // Loop
            if(!response['isStop']){
                $timeout(spin, 1000);
            }
        });
    }
    function beforeSpin(){
        $scope.isShow = true;
         _request('requestBeforeSpin', {
            action : 'beforeSpin',
            getType : $scope.slGetType
        },function (response) {
            $scope.isShow = false;
            if(response.length > 0){
                $scope.results = response;
                $scope.countPosts = response.length + ' will be spin';
            }
        });
    }
    $scope.start = function(){
        beforeSpin();
    }
    // Mark store empty description
    $scope.markStoreEmptyDescription = function(){
        _request('markStoreEmptyDescription', {
            action : 'markStoreEmptyDesc'
        },function (response) {
            console.log(response);
        });
    }
    // Count Spin remain

    $scope.checkAvaiable = function(){
        _request('requestRemain', {
            action : 'remain',
            configEmail : $scope.configEmail,
            apiKey : $scope.apiKey
        },function (response) {
            $scope.used = response['api_requests_made'];
            $scope.spinRemain = response['api_requests_available'];
        });

    }
    // Save Config
    $scope.saveConfig = function(){
        _request('requestSaveConfig', {
            action : 'saveConfig',
            configEmail : $scope.configEmail,
            apiKey : $scope.apiKey
        },function (response) {
            console.log('Save done!');
        });
    }
    // Load config
    _request('requestLoadConfig', {
        action : 'loadConfig'
    },function (response) {
        $scope.configEmail = response.spinEmail;
        $scope.apiKey = response.spinApiKey;
    });
});