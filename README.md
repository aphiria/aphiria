<h1>Todo</h1>

<h1>Notes</h1>
* Vars are either `:([a-zA-Z_][a-zA-Z0-9_]*)` or `:{([a-zA-Z_][a-zA-Z0-9_]*)(:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\([^\)]*\)+)*}`
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