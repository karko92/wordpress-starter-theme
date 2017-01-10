/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/*!*************************!*\
  !*** ./_src/js/main.js ***!
  \*************************/
/***/ function(module, exports, __webpack_require__) {

	'use strict';
	
	var _bundleUtility = __webpack_require__(/*! ./lib/bundle-utility */ 1);
	
	var loaded = (0, _bundleUtility.enqueueAll)({}); // import 'babel-polyfill';
	
	
	console.log(loaded);

/***/ },
/* 1 */
/*!***************************************!*\
  !*** ./_src/js/lib/bundle-utility.js ***!
  \***************************************/
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function(global) {'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	exports.mediaQueryQueue = undefined;
	exports.handleMediaQueries = handleMediaQueries;
	exports.enqueue = enqueue;
	exports.enqueueAll = enqueueAll;
	
	var _jquery = __webpack_require__(/*! jquery */ 2);
	
	var _jquery2 = _interopRequireDefault(_jquery);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }
	
	var mediaQueryQueue = exports.mediaQueryQueue = {};
	
	function mediaQueryListener(mediaQuery) {
	  return function listener(mql) {
	    if (!mql.matches) {
	      return false;
	    }
	
	    var actions = mediaQueryQueue[mediaQuery].actions;
	
	    actions.forEach(function (_ref) {
	      var action = _ref.action,
	          nodes = _ref.nodes,
	          selector = _ref.selector;
	      return action(nodes, selector);
	    });
	
	    return true;
	  };
	}
	
	/**
	 * Returns a function which mounts media query listeners for a given config
	 * @param  {Object} mod    A JS module instance
	 * @param  {Object} config {[media query]: [module function name]}
	 *                         Media queries matched with the name of the function to call
	 * @return {Function}      Accepts a DOM Node List and a selector
	 */
	function handleMediaQueries(mod, config) {
	  return function mediaQueryWrapper(nodes, selector) {
	    if (mod.default) {
	      mod.default(nodes, selector);
	    }
	
	    Object.keys(config).forEach(function (mediaQuery) {
	      var action = mod[config[mediaQuery]];
	      var actionItem = { action: action, nodes: nodes, selector: selector };
	
	      if (mediaQueryQueue[mediaQuery]) {
	        mediaQueryQueue[mediaQuery].actions = [].concat(_toConsumableArray(mediaQueryQueue[mediaQuery].actions), [actionItem]);
	      } else {
	        var actions = [actionItem];
	        var mql = global.matchMedia(mediaQuery);
	        mql.addListener(mediaQueryListener(mediaQuery));
	
	        mediaQueryQueue[mediaQuery] = { mql: mql, actions: actions };
	      }
	    });
	  };
	}
	
	/**
	 * Checks if a given selector has nodes in the DOM, in which case a set of actions is fired
	 * @param  {String} selector DOM elements to check for
	 * @param  {Array}  modules  Modules to execute when the elements are in the DOM.
	 * @return {Array}  The matched DOM nodes, if any
	 */
	function enqueue(selector, modules) {
	  var nodes = (0, _jquery2.default)(selector);
	
	  if (!nodes.length) {
	    return nodes;
	  }
	
	  modules.forEach(function (mod) {
	    var action = typeof mod === 'function' ? mod : mod.default;
	    action(nodes, selector);
	  });
	
	  return nodes;
	}
	
	function enqueueBySelectors(config) {
	  return Object.keys(config).reduce(function (results, selector) {
	    var modules = config[selector];
	    var nodes = enqueue(selector, modules);
	    var newResults = Object.assign({}, results);
	
	    if (nodes.length) {
	      newResults[selector] = { modules: modules, nodes: nodes };
	    }
	
	    return newResults;
	  }, {});
	}
	
	function enqueueByMediaQueries(config) {
	  return Object.keys(config).reduce(function (results, mediaQuery) {
	    var _config$mediaQuery = config[mediaQuery],
	        mql = _config$mediaQuery.mql,
	        actions = _config$mediaQuery.actions;
	
	    var actionsExecuted = mediaQueryListener(mediaQuery)(mql);
	    var newResults = Object.assign({}, results);
	
	    if (actionsExecuted) {
	      newResults[mediaQuery] = { mql: mql, actions: actions };
	    }
	
	    return newResults;
	  }, {});
	}
	
	/**
	 * Checks for nodes in the DOM for given selectors and mounts their modules as a result.
	 * It also checks against the current media query and fires a queue of matched actions.
	 * @param  {Object} config { [selector]: [...modules] }. [modules] may either be
	 *                         a function or a module object with a `default` method.
	 * @return {Object}        The results for the selectors and media queries that were matched
	 */
	function enqueueAll(config) {
	  var selectorResults = enqueueBySelectors(config);
	  var mediaResults = enqueueByMediaQueries(mediaQueryQueue);
	
	  return Object.assign({}, selectorResults, mediaResults);
	}
	/* WEBPACK VAR INJECTION */}.call(exports, (function() { return this; }())))

/***/ },
/* 2 */
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ function(module, exports) {

	module.exports = jQuery;

/***/ }
/******/ ]);
//# sourceMappingURL=main.js.map