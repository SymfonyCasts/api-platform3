# Validation

There are a *bunch* of different ways for the users of our API to mess things up,
like bad JSON or doing silly things like passing a negative number for the `value`
field. This is dragon gold, not dragon debt!

## Invalid JSON

This chapter is all about handling these bad things in a graceful way. Try the
POST endpoint. Let's send some invalid JSON. Hit Execute. Awesome! A `400` error!
That's what we want. 400 - or any status code that starts with 4 - means that the
*client* - the user of the API - made a mistake. 400 specifically means "bad
request".

In the response, the type is `hydra:error` and it says: `An error occurred`
and `Syntax Error`. Oh, and this `trace` only shows in the debug environment: it
won't be shown on production.

So this is pretty sweet! Invalid JSON is handled out-of-the-box.

## Business Rules Validation Constraints

Let's try something different, like sending *empty* JSON. *This* gives us the
dreaded 500 error. Boo. Internally, API platform creates a `DragonTreasure` object...
but doesn't set any data on it. And then it explodes when it hits the database because
some of the columns are `null`.

And, we expected this! We're missing validation. Adding validation to our API
is exactly like adding validation *anywhere* in Symfony. For example, find the
`name` property. We need `name` to be required. So, add the `NotBlank` constraint,
and hit tab. Oh, but I'm going to go find the `NotBlank` `use` statement... and change
this to `Assert`. That's optional... but it's the way the cool kids tend do it in
Symfony. Now say `Assert\NotBlank`:

[[[ code('b05489e9e8') ]]]

Below, add one more: `Length`. Let's say that the name should be at least two
characters, `max` 50 characters... and add a `maxMessage`:
`Describe your loot in 50 chars or less`:

[[[ code('5f18649857') ]]]

## How Errors Look in the Response

Good start! Let's try it again. Take that same empty JSON, hit Execute, and yes!
A 422 response! This is a really common response code that usually means there
was a validation error. And behold! The `@type` is `ConstraintViolationList`.
This is a special JSON-LD type added by API Platform. Earlier, we saw this documented
in the `JSON-LD` documentation.

Watch: go to `/api/docs.jsonld` and search for a `ConstraintViolation`. There it
is! API Platform adds two classes - `ConstraintViolation` and
`ConstraintViolationList` to describe how validation errors will look. A
`ConstraintViolationList` is basically just a collection of `ConstraintViolations`...
and then it describes what the `ConstraintViolation` properties are.

We can see these over here: we have a `violations` property with `propertyPath`
and then the `message` below.

## Adding More Constraints

Ok! Let's sneak in a few more constraints. Add `NotBlank` above `description`...
and `GreaterThanOrEqual` to `0` above `value` to avoid negatives. Finally, for
`coolFactor` use `GreaterThanOrEqual` to 0 and also `LessThanOrEqual` to 10.
So something between 0 and 10:

[[[ code('edb7d7cf25') ]]]

And while we're here, we don't need to do this, but I'm going to initialize
`$value` to 0 and `$coolFactor` to 0. This makes both of those *not* required
in the API: if the user doesn't send them, they'll default to 0:

[[[ code('343088fb55') ]]]

Ok, go back and try that same endpoint. Look at that beautiful validation! Also
try setting `coolFactor` to `11`. Yup! No treasure is *that* cool... well, unless
it's a giant plate of nachos.

## Passing Bad Types

Ok, there's one last way that a user can send bad stuff: by passing the wrong *type*.
So `coolFactor: 11` will fail our validation rules. But what if we pass a `string`
instead? Yikes! Hit Execute. Okay: a `400` status code, that's good. Though, it's
not a validation error, it has a different type. But it *does* tell the user what
happened:

> the type of the `coolFactor` attribute must be `int`, `string` given.

Good enough! This is thanks to the `setCoolFactor()` method. The system sees
the `int` type and so it rejects the string with this error.

So the only thing that we need to worry about in our app is writing good code that
properly uses `type` and adding validation constraints: the safety net that catches
business rule violations... like `value` should be greater than 0 or `description`
is required. API Platform handles the rest.

Next: our API only has one resource: `DragonTreasure`. Let's add a second resource -
a `User` resource - so that we can *link* which user owns which treasure in the API.
