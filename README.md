<h1>Todo</h1>
* Middleware depend on particular request/responses
  * I'm not sure it's possible to generalize middleware without specific request/response classes
  * So, how will middleware fit into this library?
* When actually writing this:  Should `IRouteTemplate::tryMatch()` take in a `Uri` instance?  That'd make it easier/less repetitive to grab host and path.

<h1>Notes</h1>
* Vars may look like `users/:userId' or `users/:{userId|regex(foo)|int|max(10)}`
  * It actually may require a parser to parse this syntax and account for escaped ":" and "{" and "}" chars
  * The optional rules include:
      * alpha
      * bool
      * decimal
      * double
      * guid
      * int
      * length
      * max
      * maxLength
      * min
      * minLength
      * range
      * regex
  * I feel like these rules should be extendable, so make sure they're not developed as first-class rules