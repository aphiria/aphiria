<h1>Todo</h1>
* Need a way to check if a URI matches the parsed route
  * Before, I was requiring ParsedRoute to return the regex, which feels like a break in abstraction
* Need a way to group routes with similar options
  * This is not at all obvious in my current design
* For integration testing purposes, I need to be able to grab the matched route and controller
* How do I optimize this for performance?  I need to cache the parsing of the routes as well as the mapping to controllers.
  * Need to allow for caching all the "matchers"'s pieces of data to the matched route
      * For example, need to cache host and path and map it to the matched route
* In general, make sure the skeleton project isn't having to do a ton of boilerplate - wrap this up nicely so it appeals to 3rd party users