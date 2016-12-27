<h1>Todo</h1>
* Before, I had a dispatcher, which would accept a route and request, and give you a response
  * This just feels architecturally cleaner than what I'm doing now by having the `RouteMap` have a `dispatch()` method.  Is there any way I can achieve what I'd like to do using something closer to the old method?
  * Does my new way get me anything?  IE, let's say I never had middleware before, and then introduced it, would my new solution have made that any easier?
  * Is it wise that the middleware is no longer a property of the `RouteMap`, but instead called from within the route dispatcher's middleware pipeline?
      * Is being able to grab the `RouteMap`'s middleware really a leaky abstraction?  Honestly, I'm not sure.
* Need to consider how I'll support parameterized middleware
* I need a `RouteMapRegistry` so I have a place to grab named routes from
  * Should this be injected into `Router`?  Not sure because `Router` only needs the list of all route maps, not the ability to grab named routes
* Need a way to check if a URI matches the parsed route
  * Before, I was requiring `ParsedRoute` to return the regex, which feels like a break in abstraction
* For integration testing purposes, I need to be able to grab the matched route and controller
* How do I optimize this for performance?  I need to cache the parsing of the routes as well as the mapping to controllers.
  * Need to allow for caching all the "matchers"'s pieces of data to the matched route
      * For example, need to cache host and path and map it to the matched route
* In general, make sure the skeleton project isn't having to do a ton of boilerplate - wrap this up nicely so it appeals to 3rd party users