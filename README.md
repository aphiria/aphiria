<h1>Todo</h1>
* Need to completely rewrite URI template parser to create an AST from tokens
* Need some sort of route cache
* Probably still need to add ability to chunk matching regexes for URIs rather than try to match one at a time
  * For now, I think I'll skip doing this until performance proves I need to
  * Not including this makes the architecture cleaner.  Otherwise, I'd have leaky abstraction with named regex groups for route vars, and the weirdness of generating a URI in the template but not matching in the template is bizarre