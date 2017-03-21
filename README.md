<h1>Todo</h1>

* Write URI template compiler
  * What should `compile()` accept?  If it's a concatenated host and path, how do I handle no host being passed in?
      * Is it better to force you to pass the two in separately?
  * Use some of the code and tests from `RegexUriTemplateParser` in the compiler
* Need some sort of route cache
* Probably still need to add ability to chunk matching regexes for URIs rather than try to match one at a time
  * For now, I think I'll skip doing this until performance proves I need to
  * Not including this makes the architecture cleaner.  Otherwise, I'd have leaky abstraction with named regex groups for route vars, and the weirdness of generating a URI in the template but not matching in the template is bizarre
* Need to audit all my classes and actually write tests for the ones that don't have any