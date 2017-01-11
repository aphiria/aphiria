<h1>Todo</h1>
* Need a way to check if a URI matches the parsed route
  * Before, I was requiring `ParsedRoute` to return the regex, which feels like a break in abstraction
* For integration testing purposes, I need to be able to grab the matched route and controller
  * Actually, I do not see any usages of `getMatchedRoute()`.  Do I really need this anymore?
  * For view assertions, though, I do use the matched controller.  So, this _does_ need to be implemented.
* Need to add parsed route caching (would go in place of `$routeBuilder->buildAll()`), and route matcher cache (would keep track of all the pieces of info from the request necessary to make a match)
  * How should the cached file be structured?  It'd be neat to have a bunch of nested keys to help you jump to stuff quickly
      * Will this create a jumbo file that could cause concurrency issues if too many requests come in at once?
      * As cool as route match caching would be to have, I've got a feeling it's not worth it
      * Maybe instead, it's worth just "caching" by HTTP methods (like the old router).  NOTE:  This is how I'm now doing it