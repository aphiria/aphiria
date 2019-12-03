<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Rules;

use Aphiria\Validation\Rules\Errors\Compilers\Compiler;
use Aphiria\Validation\ValidationContext;
use BadMethodCallException;
use Countable;
use InvalidArgumentException;
use Aphiria\Validation\Rules\Errors\Compilers\ICompiler;
use Aphiria\Validation\Rules\Errors\ErrorTemplateRegistry;

/**
 * Defines the rules for validation
 */
class Rules
{
    /** @var RuleExtensionRegistry The rule extension registry */
    protected RuleExtensionRegistry $ruleExtensionRegistry;
    /** @var ErrorTemplateRegistry The error template registry */
    protected ErrorTemplateRegistry $errorTemplateRegistry;
    /** @var ICompiler The error template compiler */
    protected ICompiler $errorTemplateCompiler;
    /** @var array The data used to compile error templates */
    protected array $errorSlugsAndPlaceholders = [];
    /** @var IRule[] The list of rules */
    protected array $rules = [];
    /** @var bool Whether or not a value is required */
    protected bool $isRequired = false;

    /**
     * @param RuleExtensionRegistry|null $ruleExtensionRegistry The rule extension registry
     * @param ErrorTemplateRegistry|null $errorTemplateRegistry The error template registry
     * @param ICompiler|null $errorTemplateCompiler The error template compiler
     */
    public function __construct(
        RuleExtensionRegistry $ruleExtensionRegistry = null,
        ErrorTemplateRegistry $errorTemplateRegistry = null,
        ICompiler $errorTemplateCompiler = null
    ) {
        $this->ruleExtensionRegistry = $ruleExtensionRegistry ?? new RuleExtensionRegistry();
        $this->errorTemplateRegistry = $errorTemplateRegistry ?? new ErrorTemplateRegistry();
        $this->errorTemplateCompiler = $errorTemplateCompiler ?? new Compiler();
    }

    /**
     * Attempts to call a rule extension
     *
     * @param string $methodName The method to call
     * @param array $args The arguments to pass
     * @return self For method chaining
     * @throws BadMethodCallException Thrown if no extension exists with the method name
     */
    public function __call(string $methodName, array $args): self
    {
        if (!$this->ruleExtensionRegistry->hasRule($methodName)) {
            throw new BadMethodCallException("No rule extension with name \"$methodName\" exists");
        }

        $rule = $this->ruleExtensionRegistry->getRule($methodName);

        if ($rule instanceof IRuleWithArgs) {
            $rule = clone $rule;
            $rule->setArgs($args);
        }

        $this->addRule($rule);

        return $this;
    }

    /**
     * Marks a field as having to contain only alphabetic characters
     *
     * @return self For method chaining
     */
    public function alpha(): self
    {
        $this->createRule(AlphaRule::class);

        return $this;
    }

    /**
     * Marks a field as having to contain only alpha-numeric characters
     *
     * @return self For method chaining
     */
    public function alphaNumeric(): self
    {
        $this->createRule(AlphaNumericRule::class);

        return $this;
    }

    /**
     * Marks a field as having to be between values
     *
     * @param int|float $min The minimum value to compare against
     * @param int|float $max The maximum value to compare against
     * @param bool $isInclusive Whether or not the extremes are inclusive
     * @return self For method chaining
     */
    public function between($min, $max, bool $isInclusive = true): self
    {
        $this->createRule(BetweenRule::class, [$min, $max, $isInclusive]);

        return $this;
    }

    /**
     * Marks a field as having to be a date in the input format(s)
     *
     * @param string|array $formats The expected formats
     * @return self For method chaining
     */
    public function date($formats): self
    {
        $this->createRule(DateRule::class, [$formats]);

        return $this;
    }

    /**
     * Marks a field as having to be an email
     *
     * @return self For method chaining
     */
    public function email(): self
    {
        $this->createRule(EmailRule::class);

        return $this;
    }

    /**
     * Marks a field as having to equal a value
     *
     * @param mixed $value The value that the field must equal
     * @return self For method chaining
     */
    public function equals($value): self
    {
        $this->createRule(EqualsRule::class, [$value]);

        return $this;
    }

    /**
     * Gets the error messages
     *
     * @param string $field The name of the field whose errors we're getting
     * @return array The list of errors
     */
    public function getErrors(string $field): array
    {
        $compiledErrors = [];

        foreach ($this->errorSlugsAndPlaceholders as $errorData) {
            $compiledErrors[] = $this->errorTemplateCompiler->compile(
                $field,
                $this->errorTemplateRegistry->getErrorTemplate($field, $errorData['slug']),
                $errorData['placeholders']
            );
        }

        return $compiledErrors;
    }

    /**
     * Marks a field as having to be in a list of approved values
     *
     * @param array $array The list of approved values
     * @return self For method chaining
     */
    public function in(array $array): self
    {
        $this->createRule(InRule::class, [$array]);

        return $this;
    }

    /**
     * Marks a field as having to be an integer
     *
     * @return self For method chaining
     */
    public function integer(): self
    {
        $this->createRule(IntegerRule::class);

        return $this;
    }

    /**
     * Marks a field as having to be an IP address
     *
     * @return self For method chaining
     */
    public function ipAddress(): self
    {
        $this->createRule(IPAddressRule::class);

        return $this;
    }

    /**
     * Marks a field as having a maximum acceptable value
     *
     * @param int|float $max The maximum value to compare against
     * @param bool $isInclusive Whether or not the maximum is inclusive
     * @return self For method chaining
     */
    public function max($max, bool $isInclusive = true): self
    {
        $this->createRule(MaxRule::class, [$max, $isInclusive]);

        return $this;
    }

    /**
     * Marks a field as having a minimum acceptable value
     *
     * @param int|float $min The minimum value to compare against
     * @param bool $isInclusive Whether or not the minimum is inclusive
     * @return self For method chaining
     */
    public function min($min, bool $isInclusive = true): self
    {
        $this->createRule(MinRule::class, [$min, $isInclusive]);

        return $this;
    }

    /**
     * Marks a field as having to not be in a list of unapproved values
     *
     * @param array $array The list of unapproved values
     * @return self For method chaining
     */
    public function notIn(array $array): self
    {
        $this->createRule(NotInRule::class, [$array]);

        return $this;
    }

    /**
     * Marks a field as having to be numeric
     *
     * @return self For method chaining
     */
    public function numeric(): self
    {
        $this->createRule(NumericRule::class);

        return $this;
    }

    /**
     * Gets whether or not all the rules pass
     *
     * @param mixed $value The value to validate
     * @param ValidationContext $validationContext The context to perform validation in
     * @return bool True if all the rules pass, otherwise false
     */
    public function pass($value, ValidationContext $validationContext): bool
    {
        $this->errorSlugsAndPlaceholders = [];
        $passes = true;

        foreach ($this->rules as $rule) {
            // Non-required fields do not need to evaluate all rules when empty
            if (!$this->isRequired) {
                if (
                    $value === null ||
                    (is_string($value) && $value === '') ||
                    ((is_array($value) || $value instanceof Countable) && count($value) === 0)
                ) {
                    continue;
                }
            }

            $thisRulePasses = $rule->passes($value, $validationContext);

            if (!$thisRulePasses) {
                $this->addError($rule);
            }

            $passes = $thisRulePasses && $passes;
        }

        return $passes;
    }

    /**
     * Marks a field as having to match a regular expression
     *
     * @param string $regex The regex to match
     * @return self For method chaining
     */
    public function regex(string $regex): self
    {
        $this->createRule(RegexRule::class, [$regex]);

        return $this;
    }

    /**
     * Marks a field as required
     *
     * @return self For method chaining
     */
    public function required(): self
    {
        $this->createRule(RequiredRule::class);
        $this->isRequired = true;

        return $this;
    }

    /**
     * Adds an error
     *
     * @param IRule $rule The rule that failed
     */
    protected function addError(IRule $rule): void
    {
        $this->errorSlugsAndPlaceholders[] = [
            'slug' => $rule->getSlug(),
            'placeholders' => $rule instanceof IRuleWithErrorPlaceholders ? $rule->getErrorPlaceholders() : []
        ];
    }

    /**
     * Adds a rule to the list
     *
     * @param IRule $rule The rule to add
     */
    protected function addRule(IRule $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * Adds a rule with the input name and arguments
     *
     * @param string $className The fully name of the rule class, eg "Aphiria\...\RequiredRule"
     * @param array $args The extra arguments
     * @throws InvalidArgumentException Thrown if no rule exists with the input name
     */
    protected function createRule(string $className, array $args = []): void
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Class \"$className\" does not exist");
        }

        /** @var IRule|IRuleWithArgs $rule */
        $rule = new $className;

        if ($rule instanceof IRuleWithArgs) {
            $rule->setArgs($args);
        }

        $this->addRule($rule);
    }
}
