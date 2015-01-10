<?php
	if (!in_array(cpx_get_user_role_name(), array('administrator')))
	{
		die('You do not have permission to access this page!');
	}
    wp_head();
    // CSS
    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/css/spin/bootstrap.min.css');
    // SCRIPT
    //wp_enqueue_script('angularJS', get_template_directory_uri() . '/js/libs/angular.min.js');
    wp_enqueue_script('spinScripts', get_template_directory_uri() . '/js/spin/spin.js');
?>
<title>Spin</title>
<style type="text/css">
input{padding: 10px;}
</style>
<script>
    var tempDirUri = '<?php echo get_template_directory_uri() ?>';
</script>

<body ng-app="spinApp">
    <div ng-controller="spinCtrl" class="container" >
        <h1 ng-bind = "mess"></h1>
        <h3 ng-bind = "messCalculate"></h3>
        <p>Use: {{used}} | Remain : {{spinRemain}}</p>
        <button type="button" class="btn btn-primary" ng-click = "checkAvaiable()">Check Avaiable</button>
        <p></p>

        <input type="text" ng-model="configEmail" placeholder="Enter SpinRewriter email"/>
        <input style="width: 30%;" type="text" ng-model="apiKey" placeholder="Enter SpinRewriter API Key" />
        <button type="button" class="btn btn-primary" ng-click = "saveConfig()">Save Configs</button>
        <p></p>
        <input type="text" ng-model="oldDomain" placeholder="Domain will replace" />
        <input type="text" ng-model="newDomain" placeholder="Replace with this domain" />
        <p></p>
        <input style="width: 100%;" type="text" ng-model="protectKeyword" placeholder="Protect keywords" />
        <p></p>
        <select ng-model = "slGetType" ng-init = "slGetType = 'coupons'">
            <option value="stores">Get Stores</option>
            <option value="coupons">Get Coupons</option>
        </select>
        <button type="button" class="btn btn-danger" ng-click="start()">Start</button>
        <button type="button" class="btn btn-danger" ng-click="spinNow()">Spin Now!</button>

        <button type="button" class="btn btn-primary" ng-click = "markStoreEmptyDescription()">Mark stores empty description</button>
        <label ng-bind = "countPosts"></label>
        Spined:<label ng-model = "spined" ng-bind = "spined" ng-init = "spined = 0"></label>
<!-- Loading -->
        <div id="ballsWaveG" ng-show="isShow">
            <div id="ballsWaveG_1" class="ballsWaveG">
            </div>
            <div id="ballsWaveG_2" class="ballsWaveG">
            </div>
            <div id="ballsWaveG_3" class="ballsWaveG">
            </div>
            <div id="ballsWaveG_4" class="ballsWaveG">
            </div>
            <div id="ballsWaveG_5" class="ballsWaveG">
            </div>
            <div id="ballsWaveG_6" class="ballsWaveG">
            </div>
            <div id="ballsWaveG_7" class="ballsWaveG">
            </div>
            <div id="ballsWaveG_8" class="ballsWaveG">
            </div>
        </div>
<!-- # -->
        <div class="container" style='height: 200px; overflow-y: scroll;'>
            <div ng-repeat = "r in results">
                <a ng-bind = "r" id="postID"></a>
            </div>
        </div>

    </div>
</body>

<?php wp_footer(); ?>