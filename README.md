<h1>Todo</h1>
* Really dive into how I'll set route vars (with default values), generate URIs from routes, etc
  * I cranked out a lot of code, and haven't had time to review everything
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
* Should potentially reorganize my namespaces into directories