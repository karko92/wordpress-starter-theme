import $ from 'jquery';

export const mediaQueryQueue = {};

function mediaQueryListener(mediaQuery) {
  return function listener(mql) {
    if (!mql.matches) {
      return false;
    }

    const { actions } = mediaQueryQueue[mediaQuery];
    actions.forEach(({ action, nodes, selector }) => action(nodes, selector));

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
export function handleMediaQueries(mod, config) {
  return function mediaQueryWrapper(nodes, selector) {
    if (mod.default) {
      mod.default(nodes, selector);
    }

    Object.keys(config).forEach(mediaQuery => {
      const action = mod[config[mediaQuery]];
      const actionItem = { action, nodes, selector };

      if (mediaQueryQueue[mediaQuery]) {
        mediaQueryQueue[mediaQuery].actions = [...mediaQueryQueue[mediaQuery].actions, actionItem];
      } else {
        const actions = [actionItem];
        const mql = global.matchMedia(mediaQuery);
        mql.addListener(mediaQueryListener(mediaQuery));

        mediaQueryQueue[mediaQuery] = { mql, actions };
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
export function enqueue(selector, modules) {
  const nodes = $(selector);

  if (!nodes.length) {
    return nodes;
  }

  modules.forEach(mod => {
    const action = typeof mod === 'function' ? mod : mod.default;
    action(nodes, selector);
  });

  return nodes;
}

function enqueueBySelectors(config) {
  return Object.keys(config).reduce((results, selector) => {
    const modules = config[selector];
    const nodes = enqueue(selector, modules);
    const newResults = Object.assign({}, results);

    if (nodes.length) {
      newResults[selector] = { modules, nodes };
    }

    return newResults;
  }, {});
}

function enqueueByMediaQueries(config) {
  return Object.keys(config).reduce((results, mediaQuery) => {
    const { mql, actions } = config[mediaQuery];
    const actionsExecuted = mediaQueryListener(mediaQuery)(mql);
    const newResults = Object.assign({}, results);

    if (actionsExecuted) {
      newResults[mediaQuery] = { mql, actions };
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
export function enqueueAll(config) {
  const selectorResults = enqueueBySelectors(config);
  const mediaResults = enqueueByMediaQueries(mediaQueryQueue);

  return Object.assign({}, selectorResults, mediaResults);
}
