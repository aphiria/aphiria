<h1>Todo</h1>
* Make sure URI parser checks for route vars with default values - they should be optional.  This will permit my RegexUriTemplate to work.
* Need some sort of route cache
* Probably still need to add ability to chunk matching regexes for URIs rather than try to match one at a time
  * For now, I think I'll skip doing this until performance proves I need to
  * Not including this makes the architecture cleaner.  Otherwise, I'd have leaky abstraction with named regex groups for route vars, and the weirdness of generating a URI in the template but not matching in the template is bizarre

<h1>Notes</h1>
* Vars may look like `users/:userId` or `users/:{userId|regex(foo)|int|max(10)}`

<h3>Variable Grammar</h3>
```
variable             = :routeVarName|:routeVarExpression
routeVarName         = [a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(=routeVarDefaultValue)?
routeVarDefaultValue = ([^:\[\]/]+)
routeVarExpression   = {routeVarName [|rule]*}
rule                 = ruleName[\(ruleParameters\)]?
ruleName             = [a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*
ruleParameters       = [^\)]+
```

The optional rules include:

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

I feel like these rules should be extendable, so make sure they're not developed as first-class rules