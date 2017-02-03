<h1>Todo</h1>

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