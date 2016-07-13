<?php
$hasAuth = zbase_auth_has();
$section = 'backend';
$prefix = zbase_tag();
$modules = zbase()->modules();
$isMobile = zbase_is_mobile();
$isMobileTablet = zbase_is_mobileTablet();
$routeProviders = [];
$controllers = [];
foreach ($modules as $module)
{
	$moduleRouteProviders = $module->_v('angular.mobile.' . $section . '.routeProvider', []);
	if(!empty($moduleRouteProviders))
	{
		foreach ($moduleRouteProviders as $moduleRouteProvider)
		{
			$auth = zbase_data_get($moduleRouteProvider, 'auth', true);
			if(empty($auth) && !empty($hasAuth))
			{
				continue;
			}
			if(!empty($auth) && empty($hasAuth))
			{
				continue;
			}
			$url = zbase_data_get($moduleRouteProvider, 'url', null);
			$templateUrl = zbase_data_get($moduleRouteProvider, 'templateUrl', null);
			$controller = zbase_data_get($moduleRouteProvider, 'controller', null);
			if(!empty($url) && !empty($templateUrl) && !empty($controller))
			{
				$routeProviders[] = '$routeProvider.when(\'' . $url . '\', {templateUrl : \'' . $templateUrl . '?angular=1\',controller  : \'' . $controller . '\', reloadOnSearch: false});';
			}
		}
	}
	$moduleControllers = $module->_v('angular.mobile.' . $section . '.controllers', []);
	if(!empty($moduleControllers))
	{
		foreach ($moduleControllers as $moduleController)
		{
			$auth = zbase_data_get($moduleController, 'auth', true);
			if(empty($auth) && !empty($hasAuth))
			{
				continue;
			}
			if(!empty($auth) && empty($hasAuth))
			{
				continue;
			}
			$controller = $moduleController['controller'];
			if(!empty($moduleController['view']['file']))
			{
				$controllerString = zbase_data_get($moduleController, 'view.file', null);
			}
			if(!empty($controller) && !empty($controllerString))
			{
				// $controllers[] = 'app.controller(\'' . $controller . '\', ' . $controllerString . ');';
				$controllers[] = $controllerString ;
			}
		}
	}
}
?>
<script type="text/javascript">
	(function () {
		'use strict';
		var app = angular.module('MobileAngularUi<?php echo $prefix ?>', [
			'ngRoute', 'ngCookies',
			'mobile-angular-ui',
			'mobile-angular-ui.gestures'
		], function ($interpolateProvider) {
			$interpolateProvider.startSymbol('<% ');
			$interpolateProvider.endSymbol(' %>');
		});


		app.run(function ($transform, $rootScope, $location, $cookieStore, $http) {
			window.$transform = $transform;
			// keep user logged in after page refresh
			$rootScope.globals = $cookieStore.get('globals') || {};
			if ($rootScope.globals.currentUser) {
				$http.defaults.headers.common['Authorization'] = 'Basic ' + $rootScope.globals.currentUser.authdata; // jshint ignore:line
			}
		});
<?php if(!empty($routeProviders)): ?>
			app.config(function ($routeProvider) {
	<?php echo implode("\n", $routeProviders); ?>
				$routeProvider.otherwise({redirectTo: '/'});
			});
<?php endif; ?>
<?php echo!empty($controllers) ? implode("\n", $controllers) : ''; ?>
		app.factory('flashMessengerService', flashMessengerService);
		flashMessengerService.$inject = ['$rootScope'];
		function flashMessengerService($rootScope) {
			var service = {};
			service.success = success;
			service.error = error;
			initService();
			return service;
			function initService() {
				$rootScope.$on('$locationChangeStart', function () {
					clearFlashMessage();
				});
				function clearFlashMessage() {
					var flash = $rootScope.flash;
					if (flash) {
						if (!flash.keepAfterLocationChange) {
							delete $rootScope.flash;
						} else {
							flash.keepAfterLocationChange = false;
						}
					}
				}
			}
			function success(message, keepAfterLocationChange) {
				$rootScope.flash = {
					message: message,
					type: 'success',
					keepAfterLocationChange: keepAfterLocationChange
				};
			}
			function error(message, keepAfterLocationChange) {
				$rootScope.flash = {
					message: message,
					type: 'error',
					keepAfterLocationChange: keepAfterLocationChange
				};
			}
		}
		app.factory('userService', userService);
		userService.$inject = ['$http'];
		function userService($http) {
			var service = {};
			service.getCurrentUser = getCurrentUser;
			return service;
			function getCurrentUser() {
				return $http.get('<?php echo zbase_api_url(['module' => 'account', 'object' => 'user', 'method' => 'current']) ?>');
			}
		}
		var httpInterceptor = function ($provide, $httpProvider) {
			$provide.factory('httpInterceptor', function ($q, flashMessengerService) {
				return {
					response: function (response) {
						return response || $q.when(response);
					},
					responseError: function (response) {
						if (response.status === 401) {
						}
						if (response.data !== undefined)
						{
							if (response.data.statusMessage !== undefined)
							{
								flashMessengerService.error(response.data.statusMessage);
							}
						}
						return $q.reject(response);
					}
				};
			});
			$httpProvider.interceptors.push('httpInterceptor');
			var attachAngular = function (data, headersGetter) {
				if (data === undefined)
				{
					data = {angular: 1};
				}
				return data;
			};
			$httpProvider.defaults.transformRequest.push(attachAngular);
		};
		angular.module('MobileAngularUi<?php echo $prefix ?>').config(httpInterceptor);



		//<editor-fold defaultstate="collapsed" desc="TOuchServices">
		app.directive('toucharea', ['$touch', function ($touch) {
				return {
					restrict: 'C',
					link: function ($rootScope, elem) {
						$rootScope.touch = null;
						$touch.bind(elem, {
							start: function (touch) {
								$rootScope.touch = touch;
								$rootScope.$apply();
							},
							cancel: function (touch) {
								$rootScope.touch = touch;
								$rootScope.$apply();
							},
							move: function (touch) {
								$rootScope.touch = touch;
								$rootScope.$apply();
							},
							end: function (touch) {
								$rootScope.touch = touch;
								$rootScope.$apply();
							}
						});
					}
				};
			}]);

		app.directive('dragToDismiss', function ($drag, $parse, $timeout) {
			return {
				restrict: 'A',
				compile: function (elem, attrs) {
					var dismissFn = $parse(attrs.dragToDismiss);
					return function (scope, elem) {
						var dismiss = false;

						$drag.bind(elem, {
							transform: $drag.TRANSLATE_RIGHT,
							move: function (drag) {
								if (drag.distanceX >= drag.rect.width / 4) {
									dismiss = true;
									elem.addClass('dismiss');
								} else {
									dismiss = false;
									elem.removeClass('dismiss');
								}
							},
							cancel: function () {
								elem.removeClass('dismiss');
							},
							end: function (drag) {
								if (dismiss) {
									elem.addClass('dismitted');
									$timeout(function () {
										scope.$apply(function () {
											dismissFn(scope);
										});
									}, 300);
								} else {
									drag.reset();
								}
							}
						});
					};
				}
			};
		});
		app.directive('carousel', function () {
			return {
				restrict: 'C',
				scope: {},
				controller: function () {
					this.itemCount = 0;
					this.activeItem = null;

					this.addItem = function () {
						var newId = this.itemCount++;
						this.activeItem = this.itemCount === 1 ? newId : this.activeItem;
						return newId;
					};

					this.next = function () {
						this.activeItem = this.activeItem || 0;
						this.activeItem = this.activeItem === this.itemCount - 1 ? 0 : this.activeItem + 1;
					};

					this.prev = function () {
						this.activeItem = this.activeItem || 0;
						this.activeItem = this.activeItem === 0 ? this.itemCount - 1 : this.activeItem - 1;
					};
				}
			};
		});

		app.directive('carouselItem', function ($drag) {
			return {
				restrict: 'C',
				require: '^carousel',
				scope: {},
				transclude: true,
				template: '<div class="item"><div ng-transclude></div></div>',
				link: function (scope, elem, attrs, carousel) {
					scope.carousel = carousel;
					var id = carousel.addItem();

					var zIndex = function () {
						var res = 0;
						if (id === carousel.activeItem) {
							res = 2000;
						} else if (carousel.activeItem < id) {
							res = 2000 - (id - carousel.activeItem);
						} else {
							res = 2000 - (carousel.itemCount - 1 - carousel.activeItem + id);
						}
						return res;
					};

					scope.$watch(function () {
						return carousel.activeItem;
					}, function () {
						elem[0].style.zIndex = zIndex();
					});

					$drag.bind(elem, {
						transform: function (element, transform, touch) {
							var t = $drag.TRANSLATE_BOTH(element, transform, touch);
							var Dx = touch.distanceX,
									t0 = touch.startTransform,
									sign = Dx < 0 ? -1 : 1,
									angle = sign * Math.min((Math.abs(Dx) / 700) * 30, 30);

							t.rotateZ = angle + (Math.round(t0.rotateZ));

							return t;
						},
						move: function (drag) {
							if (Math.abs(drag.distanceX) >= drag.rect.width / 4) {
								elem.addClass('dismiss');
							} else {
								elem.removeClass('dismiss');
							}
						},
						cancel: function () {
							elem.removeClass('dismiss');
						},
						end: function (drag) {
							elem.removeClass('dismiss');
							if (Math.abs(drag.distanceX) >= drag.rect.width / 4) {
								scope.$apply(function () {
									carousel.next();
								});
							}
							drag.reset();
						}
					});
				}
			};
		});

		app.directive('dragMe', ['$drag', function ($drag) {
				return {
					controller: function ($rootScope, $element) {
						$drag.bind($element,
								{
									transform: $drag.TRANSLATE_INSIDE($element.parent()),
									end: function (drag) {
										drag.reset();
									}
								},
								{
									sensitiveArea: $element.parent()
								}
						);
					}
				};
			}]);
		//</editor-fold>
	})();
</script>