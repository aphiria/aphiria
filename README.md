<h1>Todo</h1>
* Not at all sure how I'd apply my `RouteMapGroupBuilder`'s settings to all route maps created inside the callable
  * Where do I call the group builder's `build()` method?
  * Do I keep track of the group stack settings in the `RouteMapBuilderRegistry`?
  * In `RouteMapGroupBuilder`, how do I apply the settings there to all route maps built inside the callable?
  * In fact, it might not be possible.  Need to try writing some code inside `RouteMapBuilder` to see if what I'm doing is even possible
* Need a way to check if a URI matches the parsed route
  * Before, I was requiring `ParsedRoute` to return the regex, which feels like a break in abstraction
* For integration testing purposes, I need to be able to grab the matched route and controller
* How do I optimize this for performance?  I need to cache the parsing of the routes as well as the mapping to controllers.
  * Need to allow for caching all the "matchers"'s pieces of data to the matched route
      * For example, need to cache host and path and map it to the matched route
* In general, make sure the skeleton project isn't having to do a ton of boilerplate - wrap this up nicely so it appeals to 3rd party users